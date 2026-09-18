/**
 * Cloudflare Worker untuk sinkronisasi konfigurasi QuranWeb
 * Menggunakan Cloudflare KV (binding: QURAN_SYNC)
 *
 * Endpoint:
 * - GET  /health      : Cek status worker
 * - POST /sync        : Buat sync code baru (otomatis 6 digit acak ATAU custom code dari user)
 * - GET  /sync/:code  : Ambil data konfigurasi berdasarkan kode
 * - PUT  /sync/:code  : Update data konfigurasi untuk kode yang sudah ada
 */

const CORS_HEADERS = {
  'Access-Control-Allow-Origin': '*',
  'Access-Control-Allow-Methods': 'GET, POST, PUT, OPTIONS',
  'Access-Control-Allow-Headers': 'Content-Type, Authorization',
  'Access-Control-Max-Age': '86400',
};

// Karakter acak tanpa huruf/angka yang ambigu visual (tanpa O, 0, I, 1)
const CODE_CHARS = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
const DEFAULT_CODE_LENGTH = 6;
// Format kode yang diperbolehkan: 4 sampai 12 karakter alfanumerik (A-Z, 0-9)
const CODE_REGEX = /^[A-Z0-9]{4,12}$/;
const MAX_PAYLOAD_BYTES = 65536; // 64 KB
const TTL_SECONDS = 86400 * 180; // 180 hari

/**
 * Generate acak 6-digit alphanumeric code
 *
 * @return string
 */
function generateRandomCode() {
  let code = '';
  const randomValues = new Uint8Array(DEFAULT_CODE_LENGTH);
  crypto.getRandomValues(randomValues);

  for (let i = 0; i < DEFAULT_CODE_LENGTH; i++) {
    code += CODE_CHARS[randomValues[i] % CODE_CHARS.length];
  }
  return code;
}

/**
 * Helper response JSON dengan CORS headers
 *
 * @param object data
 * @param int status
 * @return Response
 */
function jsonResponse(data, status = 200) {
  return new Response(JSON.stringify(data), {
    status,
    headers: {
      ...CORS_HEADERS,
      'Content-Type': 'application/json; charset=utf-8',
    },
  });
}

export default {
  async fetch(request, env, ctx) {
    // 1. Handle CORS preflight
    if (request.method === 'OPTIONS') {
      return new Response(null, {
        status: 204,
        headers: CORS_HEADERS,
      });
    }

    // Pastikan binding KV tersedia
    if (!env.QURAN_SYNC) {
      return jsonResponse({ error: 'KV namespace QURAN_SYNC belum dikonfigurasi' }, 500);
    }

    const url = new URL(request.url);
    const path = url.pathname.replace(/^\/+|\/+$/g, '');
    const segments = path.split('/');

    // 2. Health check endpoint
    if (request.method === 'GET' && (path === '' || path === 'health')) {
      return jsonResponse({
        status: 'ok',
        service: 'quranweb-sync',
        timestamp: new Date().toISOString(),
      });
    }

    // 3. GET /sync/:code -> Ambil data konfigurasi
    if (request.method === 'GET' && segments[0] === 'sync' && segments[1]) {
      const code = segments[1].toUpperCase();

      if (!CODE_REGEX.test(code)) {
        return jsonResponse({ error: 'Format kode tidak valid (harus 4-12 karakter alfanumerik)' }, 400);
      }

      const stored = await env.QURAN_SYNC.get(code, 'json');
      if (!stored) {
        return jsonResponse({ error: 'Kode sinkronisasi tidak ditemukan atau telah kedaluwarsa' }, 404);
      }

      return jsonResponse({
        success: true,
        code,
        data: stored.data || stored,
        updatedAt: stored.updatedAt || null,
      });
    }

    // 4. POST /sync -> Buat kode baru (Acak atau Custom Input dari User)
    if (request.method === 'POST' && segments[0] === 'sync' && segments.length === 1) {
      let body;
      try {
        const rawText = await request.text();
        if (rawText.length > MAX_PAYLOAD_BYTES) {
          return jsonResponse({ error: 'Ukuran data melebihi batas maksimum (64 KB)' }, 413);
        }
        body = JSON.parse(rawText);
      } catch (e) {
        return jsonResponse({ error: 'Format JSON tidak valid' }, 400);
      }

      let code = '';
      let syncData = body;

      // Cek apakah user menentukan custom code sendiri
      const requestedCode = (body && typeof body.code === 'string') ? body.code.trim().toUpperCase() : '';

      if (requestedCode !== '') {
        // Validasi format custom code
        if (!CODE_REGEX.test(requestedCode)) {
          return jsonResponse({
            error: 'Format kode kustom tidak valid. Harus 4-12 karakter alfanumerik (A-Z, 0-9) tanpa spasi atau simbol.',
          }, 400);
        }

        // Cek apakah custom code tersebut sudah terdaftar
        const existing = await env.QURAN_SYNC.get(requestedCode);
        if (existing) {
          return jsonResponse({
            error: `Kode "${requestedCode}" sudah digunakan. Silakan gunakan kode lain atau hubungkan dengan kode yang sudah ada.`,
            codeTaken: true,
          }, 409);
        }

        code = requestedCode;

        // Pisahkan data konfigurasi dari properti `code`
        if (body.data) {
          syncData = body.data;
        } else {
          syncData = { ...body };
          delete syncData.code;
        }
      } else {
        // User tidak memasukkan kode -> generate acak 6 digit unik
        if (body && body.data) {
          syncData = body.data;
        }

        for (let attempt = 0; attempt < 5; attempt++) {
          const candidate = generateRandomCode();
          const exists = await env.QURAN_SYNC.get(candidate);
          if (!exists) {
            code = candidate;
            break;
          }
        }

        if (!code) {
          return jsonResponse({ error: 'Gagal membuat kode unik acak, silakan coba lagi' }, 500);
        }
      }

      const payload = {
        data: syncData,
        createdAt: new Date().toISOString(),
        updatedAt: new Date().toISOString(),
      };

      await env.QURAN_SYNC.put(code, JSON.stringify(payload), {
        expirationTtl: TTL_SECONDS,
      });

      return jsonResponse({
        success: true,
        code,
        message: 'Kode sinkronisasi berhasil didaftarkan',
      }, 201);
    }

    // 5. PUT /sync/:code -> Perbarui data yang ada
    if (request.method === 'PUT' && segments[0] === 'sync' && segments[1]) {
      const code = segments[1].toUpperCase();

      if (!CODE_REGEX.test(code)) {
        return jsonResponse({ error: 'Format kode tidak valid (harus 4-12 karakter alfanumerik)' }, 400);
      }

      const existingRaw = await env.QURAN_SYNC.get(code);
      if (!existingRaw) {
        return jsonResponse({ error: 'Kode sinkronisasi tidak ditemukan atau telah kedaluwarsa' }, 404);
      }

      let body;
      try {
        const rawText = await request.text();
        if (rawText.length > MAX_PAYLOAD_BYTES) {
          return jsonResponse({ error: 'Ukuran data melebihi batas maksimum (64 KB)' }, 413);
        }
        body = JSON.parse(rawText);
      } catch (e) {
        return jsonResponse({ error: 'Format JSON tidak valid' }, 400);
      }

      let existing = {};
      try {
        existing = JSON.parse(existingRaw);
      } catch (e) {}

      const syncData = (body && body.data) ? body.data : body;

      const payload = {
        data: syncData,
        createdAt: existing.createdAt || new Date().toISOString(),
        updatedAt: new Date().toISOString(),
      };

      await env.QURAN_SYNC.put(code, JSON.stringify(payload), {
        expirationTtl: TTL_SECONDS,
      });

      return jsonResponse({
        success: true,
        code,
        message: 'Data berhasil diperbarui',
      });
    }

    return jsonResponse({ error: 'Endpoint tidak ditemukan' }, 404);
  },
};
