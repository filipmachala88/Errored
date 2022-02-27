(function($){

    /*
        JSON
        - často se stává, že potřebujeme mít informaci k vícero informacím
        - často potřebujem přeposílat informace z PHP do JS (a naopak)
        k tomu slouží JSON formát
        - způsob zápisu kolekce (více) dat
        - lehce se pracuje s jeho hodnotami např. v JS
        - dá se používat v JS (umí s ním praocvat přímo) i v PHP (neumí s ním pracovat přímo -> potřeba použít funkci na přetransformování)
        exituje více způsobů jak data zabalit do balíčku, avšak JSON se stal STANDART

        PHP zápis pole:
            [
                'proměnná' => 'hodnota',
                'proměnná' => 'hodnota'
            ]
        JSON zápis:
            {
                'proměnná' : 'hodnota',
                'proměnná' : 'hodnota' 
            }
        
        existuje způsob jak udělat z PHP zápisu JSON zápis:
            - z PHP do JSON: json_encode( proměnná );
            - z JSON do PHP: json_decode( proměnná, true );
    */
    var data = {
        "error": {
            "status" : "succes",
            "id" : "15"
        }
    };

    /* způsob zápisu, jak se dostat k datám JSONu */
    // console.log( data.error.status );
    // console.log( data.error.id );

    /**
     * INSERT FORM
     */

    var form  = $('#add-form'),
        /* označíme si prvek - nyní formulář s id="add-form" */
        list  = $('#item-list'),
        /* nyní <ul> element v index.php */
        input = form.find('#text');
        /*
            označíme si prvek - nyní input s id="text"
            - chceme provést jen na stránce s todoapp (index), na edit nechceme - jinak nebudeme mít předvyplněný formulář
            find() = pouze ten #text který spadá pod #add-form formulář se bude mazat
        */
    
    // clear input after send
    input.val('').focus();
    /* 
        val('') = hodnota inputu se nastaví na prázdný string (vymaže se vše)
        .focus() = vyznačí ho (jakobych do něj klikl - můžu dále psát)

        - umístění na začátek kódu umožní psát do inputu ihned po načtení stránky (nemusíme tam klikat, můžeme hned psát)
    */
    
    $('.submit-button').hide();
    /* nechám schovat tlačítko, pokud funguje JS */
    /**
     *  SETTINGS
     */
    // array
    var animation = {
        startColor: '#00bc8c',
        endColor: list.find('li').css('backgroundColor') || '#ffffff',
        /* 
            natvrdo napsané barvy
            - pokud změním barvu elementů (např. barvy přehodím), text by nemusel jít vidět
            - lepší bude když zjistím jakou barvu ty elementy mají a podle toho nechám zanimovat
            UPDATE: nyní CSS kontroluje design stránky, a JS animace určí hodnotu podle CSS (pokud změním design v CSS, změna se odrazí i v JS + vím, kde to najdu)

            list.find() = v rámci seznamu najdi li element
            .css() = nechán si vytáhnout jakou mají barvu pozadí (backgroundColor)
            || = or/nebo
                - pokud se nepodaří (najít li element a vytáhnout barvu) první část kódu (co je před ním)
                - provede se druhá (co je za ním) - ať máme nějakou rezervní barvu (Fallback hodnotu)
        */
        delay: 200
    }

    form.on('submit', function(event) {
        /* pokud se formulát odešle (submit) */
        event.preventDefault();
        /* 
            tak se zastaví akce (to co jsme definovali v configu - request na podstránku atd.), kterou má prohlížeč definovanou (našim kódem)
            - jenže chceme, aby vše provedl na pozadí - provedeme to přes jS
         */
        var req = $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: form.serialize(),
            dataType: 'json'
            /* očkávám, že se mi vrátí z tohoto requestu dataType: json */
        });
        /*
            req = chceme vytvořit request, vytvoříme si proměnnou
            .ajax() = předdefinovaná funkce, chceme aby request byl AJAXový, ale NA POZADÍ
            url: = adresa, nakterou se má request provést
                form = víme, že máme formulář (který jsme si označili nahoře pod proměnnou "form" díky třídě), ten má v atributě action="" kam se má request provést
                attr('typ') = vytáheme atribut z formuláře, náš typ atributu je 'action'
            type: = metoda requestu (POST/GET), pro nás 'POST'
            data: = k requestu chceme přidat data
                - buď je můžeme vypsat nebo použít serialize()
                serialize() = jQuery funkce, vezme data ze všech inputů + textarea a udělá z nich celek
                form. = připnutím k formuláži vezme všechna data konkrétně z něj
        */
        req.done(function(data){
                /* 
                    kontrola
                    (to co se objevuje na stránce add-item.php)
                    - před přidáním JSONu by se mělo vypsat pouze "succes"
                    - po přidání by se měla vypsat celá data JSONU -> "succes" a "id"
                    nyní se "succes" nachází v data.status -> musíme přepsat v podmínce
                */
                // console.log(data);
                /* 
                    kontrola
                    - data.status = dostaneme se ke JSON hodnotě status
                    - data.id = dostaneme se ke JSOn hodnotě id
                */
                // console.log(data.status);
                // console.log(data.id);
            /*    
                req.done() = pokud je request úspěšně ukončený
                function(data) = chceme spustit funkci, kde necháme odchytit data
                - tyto data (které se mi vrátily) si necháme vypsat do konzole
                
                pokud provedeme request na adresu, v Prozkoumat/Network/Response můžeme vidět, jaká data se vrátily
                - jako data se vrátí kompletní HTML kód naší stránky
                - takže pokud my provedeme request na adresu, vrátí se nám výsledky stránky - HTML kód dané stránky (vypíše se do konzole)
                - prohlížeč se JavaScriptem na pozadí podívá na add-new.php soubor (jako "data") a vypíše ho                   
            */
           if ( data.status === 'success' ){
                /*  pokud se data odešlou - text na stánce je "succes" */
                $.ajax({ url: baseURL }).done(function(html){
                    var newItem = $(html).find('#item-' + data.id);
                    // data control
                    // console.log(newItem[0]);
                    newItem.appendTo(list)
                    .css({backgroundColor: animation.startColor})
                    .delay(animation.delay)
                    .animate({backgroundColor: animation.endColor});
                    /* 
                        .css() = přenastavím CSS, tykát se bude barvy pozadí elementu při přidání
                        .delay(200) = 200ms pauza/prodleva (zastavíme)
                        .animate({}) = provedu animaci (změnu barvy - problikne)

                        - pokud máme v kóde natvrdo napsané hodnoty, které se můžou měnit, není ideální, když jsou rozmístěné různě v kódě
                        - když je pak budem chtít změnit -> budeme muset hledat, na jakém řádku v jakém souboru byly
                        - lepší je si to dát vše do jednoho souboru a vím, že tam jsou globální proměnné (pro nás config.php)
                        - vytvořím si pro kód proměnné a definuju si ji na začátku kódu
                    */
                    input.val('').focus();
                    /* 
                        $.ajax() = vytvoříme nový ajax request a bude na domovskou stránku - stránka se jmenuje /todoapp
                            - změnit, při nahrání na server přestane fungovat
                            - chceme mít v JS přístup k hodnotě (globální proměnné), co jsme si vytvořili v config.php
                            - PHP kód nedokáže přečíst proměnné z JS souboru
                            - můžeme však v HTML přidat <script> kde bude má přístup PHP
                        .done() = pokud to bude hotové (request se úspěšně ukončí), spustím funkci a v ní se vrátí html kód té podstránky
                        $(html).find() = v ní si pak můžu vyhledat ten li element, který je poslední
                        UPDATE:
                            - víme, že itemy <li> v seznamu <ul> mají nastavené id="item-id itemu v databázi"
                            - místo li:last-chidl můžeme poslední item odchytit tak, že si necháme najít #item- a přidáme k němu id přidaného elementu (máme uložené v JSONu)
                        appendTo() = necháme přilepit k prvku co má třídu .list-group (což je náš <ul> v index.php, JS musí najít -> je lepší si udělat proměnnou a pak ji zde použít)
                        
                        - nechám kód animací zobrazit a vyčistím input
                    */
                });
                
                /* 
                    var li = $('<li class="list-group-item">' + input.val() + '</li>');

                    - (manuálně) se vytvoří <li> element s hodnotou
                    UPDATE: tento kód je zodpovědný za přidání nového itemu do stránky
                    - chybí zde edit a delte linky (neudělal jsem opakovaný request / nerefreshl jsem), nyní JS přestává stačit
                    - problém můžeme spravit AJAXEM -> prohlížeč necháme se podívat na hlavní stránku -> zjistíme, který byl poslední item -> ochytíme a přidáme do stránky

                    input.val() = hodnota (string) proměnné input (tam jsme si definovali náš input s id="text")
               
                    li.hide()
                        .appendTo('.list-group')
                        .fadeIn();
                 
                    .hide() - nechám ho na začátku skrýt, aby nešel vidět
                    .appendTo() - přidám ho na konec seznamu
                    .fadeIn() - nechám ho se plynule objevit
                */
           }

        })

    });

    input.on('keypress', function(event){
        if (event.which === 13){
            form.submit();
            return false;
        }
        /*
            'keypress' = pokud kliknu na klávesu
            event.which = s označením 13 - enter (každá klávesa je v PC spojená s číselným označením)
            form.submit() = přinutím formulář, aby se odeslal (manuálně) - způosobí to stejné jakobych klikl na tlačítko
            return false = zabrání, aby se enter (který jsme zmačkli pro odeslání) odeslal společně se zprávou (nepřipíše se nakonec)
        */
    });

    /**
     * EDIT FORM
     */
    $('#edit-form').find('#text').select();
    /*
        když prijdu na stránku pro editaci, musím si klikat zbytečně do inputu
        - chci mít automaticky předvybrané, abych mohl začít psát

        $() - vyberu si prvek
        .find() - pokud existuje identifikator #text
        .select() - tak zavolam jQuery funkci, která mi vybere obsah formuláře (jakobych do ní klikl)

        - bylo by dobré udělat průzkum, zda li lidé pro změnu příspěvku píšou ihned -> předvyznačíme, nebo klikají nakonec řádku a pak píší/mažou -> dáme jim kuzor nakonec
        - hlavní je o tomto přemýšlet a uvažovat nad takovými věcmi
        
        FEATURES
        - důležité je abychom uvažovali nad pointou každné jedné feature
        - pokud můžeme dopomoci uživateli, aby se k pointě dostal rychleji (v tomto případě úprava todo prvku), tak to udělejme
        - můžeme vidět na stránce, že tlačítko "upravit" u našeho edit listu je větší, než "zpět" -> pointa je, že chceme upravit položku
            - když si zadefinuzjeme pointu, vytvoří se nám priority (jde o editaci -> tlačítko na editaci bude větší -> oči budou automaticky přitáhnuté na co budeme klikat 90% času)
            - méně duležité prvky budou i méně vizuálně výrazné
    */

    /**
     * DELETE FORM
     */
    $('#delete-form').on('submit', function(event){
        return confirm('for sure?');
    });
    /*
        $() - vyberu si formulář
        .on() - pokud kliknu na submit returnem se vrátí hodnota z confirm()
        confirm() - vyskakovací potvrzovací okno, tlačítka -> OK/CANCEL
        - pokud kliknu na OK (je to definitivní), odkáže na delete-item a proběhne funkce smazání
        - aby se nemohl ukliknou a vše si smazat
    */
}(jQuery));
/* 
    - jQuery může prvně svítit červené - nevložil jsem do kódu, nemůžu s ním pracovat
    
    Globálně nainstalujeme bower přes npm a jQuery přes bower
    - v bower_components/dist si můžeme vybrat vezi (plný / minifikovaný .min) souboru
    - vybereme plný (jquery.js) a přesuneme si do složky s JS
    - přidáme <script> cesty, abychom ho mohli používat

    Při práci s JS je dobré používat #id, mají své výhody:
    - rychlejší vyhledávání v JS
    - unikátní identifikátor pro prvek (v případě více prvků)
    - class lepší pro CSS, id lepší pro JS
*/