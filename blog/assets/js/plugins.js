/*
    settings & variables
*/
/* druhý způsob na získání URL adresy do JS */

if (typeof window.location.origin === 'undefined'){
    window.location.origin = window.location.protocol + '//' + window.location.host;
}

function getRootUrl(){
    // vrátí cestu k server
    return window.location.origin;
}

function getBaseUrl(){
    // vrátí cestu k homepage aplikaci
    var re = new RegExp(/^.*\//);
    return re.exec(window.location.href)[0];
}
/* kontrola */
// console.log(getRootUrl());
// console.log(getBaseUrl());
/* 
    soubor pro všechny funkce (kód) nalezený na internetu
    - byl by tu color-animation (abych mohl upravovat i barvy), ale ja ho nainstalovat přes bower
*/
