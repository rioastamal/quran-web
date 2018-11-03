document.getElementById('translation').onclick = function(e)
{
    var translationClass = 'ayah-translation';

    if (!this.checked) {
        translationClass = 'ayah-translation hide';
    }

    var translationElements = document.getElementsByClassName('ayah-translation');
    for (var i=0; i<translationElements.length; i++) {
        translationElements[i].className = translationClass;
    }
}

document.getElementById('nightmode').onclick = function(e)
{
    var nightModeCss = {
        default: 'nightmode',
        light: 'nightmode-light',
        lineMenu: 'nightmode-linemenu',
        linkBlend: 'nightmode-link-blend'
    };

    var addOrRemoveNightMode = function(isNight, elClassName, nightName) {
        var elements = document.getElementsByClassName(elClassName);

        if (!isNight) {
            for (var i=0; i<elements.length; i++) {
                elements[i].className = elements[i].className.replace(nightName, '');
            }

            return;
        }

        for (var i=0; i<elements.length; i++) {
            elements[i].className = elements[i].className + ' ' + nightName;
        }
    };

    var targetElements = [
        'ayah-text', 'surah-index',
        'sidepage', 'ayah-number',
        'footer', 'surah-index-link',
        'linemenu'
    ];
    var targetCss = [
        nightModeCss.light, nightModeCss.light,
        nightModeCss.default, nightModeCss.default,
        nightModeCss.default, nightModeCss.linkBlend,
        nightModeCss.lineMenu
    ];

    if (!this.checked) {
        document.body.className = document.body.className.replace(nightModeCss.default, '');

        for (var i=0; i<targetElements.length; i++) {
            addOrRemoveNightMode(false, targetElements[i], targetCss[i]);
        }

        return;
    }

    document.body.className = document.body.className + ' ' + nightModeCss.default;
    for (var i=0; i<targetElements.length; i++) {
        addOrRemoveNightMode(true, targetElements[i], targetCss[i]);
    }
}