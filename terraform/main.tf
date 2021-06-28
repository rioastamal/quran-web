terraform {
  required_version = "~> 1.0.0"

  required_providers {
    cloudflare = {
      source = "cloudflare/cloudflare"
      version = "2.15.0"
    }
  }

  backend "remote" {
    organization = "quranweb"

    workspaces {
      name = "domain"
    }
  }
}

variable "cf_api_token" {
  type = string
}

variable "cf" {
  type = map
}

provider "cloudflare" {
  api_token = var.cf_api_token
}

# quranweb.id -> netlify
resource "cloudflare_record" "netlify" {
  zone_id = var.cf.zone_id
  name = var.cf.domain
  type = "CNAME"
  value = var.cf.netlify_domain
  proxied = true
}

# www.quranweb.id -> netlify
resource "cloudflare_record" "www_netlify" {
  zone_id = var.cf.zone_id
  name = "www.${var.cf.domain}"
  type = "CNAME"
  value = var.cf.netlify_domain
  proxied = true
}