# 108 - Login, PHP autentifikace, autorizace, HASH funkce

- AUTHENTICATION (autentifikace) - Registrace, přihlašování atd.
- AUTHORIZATION (autorizace) - práva na určité věci (admin - přidávat články, guest - sledovat profily)

Komplexní záležitost (komplikované a je jednoduché to pokazit - můžeme se stát obětí útoků)
- šifrování hesel do databáze
- kontrola přenášená stavu přihlášení ze stránky na stránku (z requestu na request)
- přihlášení se ukládá v sesssion a cookies (uživatel má k nim přístup - může je měnit), neukládají se přímo data, ale HASH

HASH 
- zakódovaná sekvece znaků, která je unikátní pro každého uživatele (usera) a nedá se rozkódovat
- vždy když zadám stejný vstup, zakóduje se do steného výstupu (nejsou náhodné, ale vznikli z věty)
- nerozkódovatelný zpět (neexistuje reversní funkce)
- výstup (HASH) má většinou pevnou délku (vždy stejnou) bez ohledu na délce vstupu (proto se využívá CHAR 40 - vždy bude obsahovat 40 znaků)
> pomocí hashovací funkce si necháme zakódovat proměnnou

```php
$name = "Petr Král";
// zakódování pomocí hashovací funkce
$hash = md5( $name );

// výpis
echo "<pre>";
print_r($hash);
echo "</pre>";
// output: e89b9427e80a242239d870c74374b35a

// délka výstupu HASH zakódování
echo strlen($hash);
// output: 32

// bezpčenější hash
$hash = password_hash( $name, PASSWORD_BCRYPT );
// porovnává hodnotu formuláře s hashem vytáhnutého z databáze - true/false
password_verify($name, $hash);
```

šifrování znaků (tvorba hashe)
- přehážou se, některé se odstřihnou + algoritmus smíchání
- tomuto odvětví se říká kryptografie

nikdy nepoužívat POUZE funkce sha1 nebo md5
- pokud bych si chtěl vytvořit sám - vyhledat BCRYPT nebo BLOWFISH (v PHP i password_hash funkce - vygeneruje bezpečnější HASH)

takto se vytváří hesla (uloží se do sesssion a databáze, na základě toho (HASHU) si vytáhneme z databáze údaje o přihlášení)
- při registraci se heslo zaHASHuje do stringu a ten se uloží v databázi
- při logině když napíši heslo string se zakóduje přes stejnou funkci a porovná se s vytáhnutým heslem z databáze
- proto neexistuje dostat staré heslo nazpět, protože stránka ho neví (je uložený HASH - nejde rozkódovat) - musí se vytvořit nové a pošlou ho (password link)

Nebudeme si vytvářet vlastní - použijeme Package
- pokud budeme hledat klíčové slovo bude "Auth"
- většina může být pro frameworky

# 109 - Autorizační, Autentifikační balíčky
Tyto jsou samostatné
- [uFlex](https://github.com/ptejada/uFlex) 
- [Sentinel](https://cartalyst.com/manual/sentinel/6.x)
- [Huge](https://github.com/panique/huge)
- [PHP Auth](https://github.com/PHPAuth/PHPAuth)

PHP Auth (menší a jednodušší)
- dříve nebylo přidatelné přes Composer
- umí přihlašovat podle emailu, ale nemá user name - musíme si dodělat
- umí kontrolovat počet přihlášení (po 5 přihlášeníchh zablokuje), pokud nechci - musím upravit v kódu (odstranit)
- pokud nechci, aby se odesílal aktivační link po registraci (ale byl ihned aktivní) - musím upravit
- nemá dokonalou dokumentaci (včetně příkladů)

> každý framework má v šobě autorizační a autentifikační systém zabudovaný

# 110 - Nastavení PHP Auth, PART 1

Použijeme PHP Auth
> nyní lze přidat přes Composer
```DOS
_inc/composer require phpauth/phpauth:dev-master
```
> ze složky _inc/vemdor/PHPAuth si naimportujeme soubor `database.sql` do naší databáze "blog" - vytvoří tabulky:
> - attempts - zde se ukládají pokusy o přihlášení
> - requests - žádosti o registraci/přihlášení
> - sessions - kvůli bezpečnosti se často ukládají do databáze (přes HASH), který uživatel je přihlášen (používá "user_id", můžeme spravit join do tabulky "users")
> - config - nastavení samotného package 

údaje z config tabulky (můžeme upravit na vlastní)
- adresa aktivačního emailu
- adresa zpět na naši stránku
- cookie nastavení
- kam se mají ukládat data
- časová zóna
- stránky (podstránky) aktivace účtu a restartu hesla
- údaje odkud se odesílají aktivační údaje a reset hesel

### Zjištení jak aplikace funguje

Nemáme dokumentaci, tudiž vše co víme je hláška "You are currently locked out of the system"
- v adresáři _inc/vendor/PHPAuth/languages/en.php máme chybové hlášky - vidíme, že hláška je asociovaná s klíčem "user_blocked"

vyhledáme si, kde všude se používá a zjistím v `auth.class.php`
> ve funkci `login()` zjistím, že "user_blocked" je chybová hláška, která se vyhodí pokud neprojdu přes funkci `isBlocked()`
```php
if ($this->isBlocked()) {
    $return['message'] = $this->lang["user_blocked"];

    return $return;
}
```
> ve funkci `isBlocked()` nejprve získáme ip adresu a pro ni count z tabulky table_attempts (pro tuto ip adresu)
```php
$ip = $this->getIp();

$query = $this->dbh->prepare("SELECT count, expiredate FROM {$this->config->table_attempts} WHERE ip = ?");
$query->execute(array($ip));
```
> fetchne se a získáme count
```php
$row = $query->fetch(PDO::FETCH_ASSOC);

$expiredate = strtotime($row['expiredate']);
$currentdate = strtotime(date("Y-m-d H:i:s"));
```
> zkontrolujeme jestli je 5 a zároveň neuběhla určitá doba - vráí true (jsem zablokovaný - isBlocked)
```php
if ($row['count'] == 5) {
    if ($currentdate < $expiredate) {
        return true;
    }
    $this->deleteAttempts($ip);
    return false;
}
```
> pokud uběhla určitá doba z tabulky se smažou záznamy pro danou IP
```php
if ($currentdate > $expiredate) {
    $this->deleteAttempts($ip);
}
```

Proto bychom měli psát čitatelné funkce a jasné názvy, aby jsme se v něm mohli orientovat (i ti co to budou po nás číst)

## Zrušení kontroly

1. vyhledat a zakomentovat všechhny funkce, kde se používá funkce `isBlocked()`
2. navrh samotné funkce `isBlocked()` vložíme `return: false;`

Takto bychom mohli vyskávat části funkcí a odstraňovali je kompletně (se vším co se nám na knihovně nelíbí - dokud bychom neměli vlastní)
- takovéto přímé vstupy do knihovny by se neměli dělat (speciálně pokud jsme použili composer - composer update staré změny přepíše s novou verzí souboru)
- pokud však máme vlastní soubor (např. zip), tak se nezmění

# 111 - Nastavení PHPAuth, PART 2

v [Github issues](https://github.com/PHPAuth/PHPAuth/issues) se můžeme dočíst, odpovědí na naše otázky a problémy
- zde najdeme kód [jak se přihlásit](https://github.com/PHPAuth/PHPAuth/issues/52) a [jak zkontrolovat, jestli jsem přihlášený](https://github.com/PHPAuth/PHPAuth#how-to-secure-a-page)

> do `config.php` vložíme přihlášení - změníme cesty a `include()` (používá se na šablony) na `require_once()` (na kusy kódu)
> - nacházíme se již v config, nemusíme ho tedy zadávat do linku
```php
require_once("vendor/phpauth/phpauth/Config.php");
require_once("vendor/phpauth/phpauth/Auth.php");
```
> přístup do databáze `$db` už máme - můžeme nechat jen naši
> - již duplikujeme hodnotu `$config` - přejmenujeme na `$auth_config` (vždy když budu vkládat kód z balíčku s `$config` - musím přepsat)
> - můžeme zkontrolovat
```php
$auth_config = new PHPAuth\Config($db);
$auth   = new PHPAuth\Auth($db, $auth_config);

// data control
var_dump( $auth_config );
var_dump( $auth );
```

# 112 - Registrace uživatele, odeslání emailů přes PHP

Pokud se podíváme na funkci `register()`, tak vidíme parametry: `$email`, `$password` a `$repeatpassword`
- toto jsou proměnné, které jsou potřebné poslat, abychom vytvořili registraci (formulář bude potřebovat 3 inputy)

> Vytvoříme nový soubor `register.php` a v něm registrační formulář
```php
<?php include_once "_partials/header.php" ?>

	<form method="post" action="" class="box box-auth">
		<h2 class="box-auth-heading">
			Register, you dumbass
		</h2>

		<input type="text" value="" class="form-control" name="email" placeholder="Email Address" required autofocus>
		<input type="password" class="form-control" name="password" placeholder="Password" required>
		<input type="password" class="form-control" name="repeat" placeholder="Password again, DO IT" required>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Register</button>

		<p class="alt-action text-center">
			or <a href="<?= BASE_URL ?>/login">come inside (of me)</a>
		</p>
	</form>

<?php include_once "_partials/footer.php" ?>
```
> Abychom se k souboru dostali, musíme přidat route v `index.php`
```php
 // LOGIN
"/login" => [
    "GET"  => "login.php",             // login form
    "POST" => "login.php",             // do login
],
// REGISTER
"/register" => [
    "GET"  => "register.php",         // register form
    "POST" => "register.php",         // do register
],
```
Nyní můžeme zkontrolovat na adrese localhost/blog/register
> v `register.php` zkontrolujeme jaký typ requestu to byl
> - v případě, že POST - vím, že se snaží odeslat registrační formulář
```php
// register from submitted
if ( $_SERVER["REQUEST_METHOD"] === "POST" )
{
		
}
```
> pokud odešleme formulář na stejnou adresu má výhodu - pokud chceme předvyplnit hodnoty, tak nemusím používat session
> - rovnou můžu postlat hodnotu POST (poslal jsem si ho na stejnou adresu - mám k němu rovnou přístup)
> - hesla se nepředvyplňují
```php
<input type="text" value="<?php echo $_POST["email"] ?: "" ?>" class="form-control" name="email" placeholder="Email Address" required autofocus>
```
> chci si nechat odchytit email, heslo a opakovane heslo
> - nechám proběhnou přes filtr a nechám sanitiznout na email
> - hesla sanitizovat nechci - lidé rádi používají různé znaky jako heslo
> - měli bychom validovat, když zpracováváme údaje z formuláře, ale knihovna již dělá za nás v register() funkci
```php
// register from submitted
$email = filter_input( INPUT_POST, "email", FILTER_SANITIZE_EMAIL );
$password = $_POST["password"];
$password_repeat = $_POST["repeat"];
```
> můžeme zkusit zavolat registrační funkci
```php
$register = $auth->register($email, $password, $password_repeat);

// register data output
echo "<pre>";
print_r($_POST);
echo "</pre>";
```
> registrace může vyhodit hlášky jako "slabé heslo" - najdeme funkci validatePassword() a upravíme - co nechceme zakomentujeme 

> necháme zobrazit error hlášky knihovny
```php
if ($register["error"])
{ 
    flash()->error($register["message"]);
}
```
Nyní se vytvoří v databázi účet v tabulce phpauth_users
- pokud bychom chtěli změnit tabulku, můžeme změnit v tabulce (nastavení knihovny) phpauth_config

# 113 - Login, přihlášení uživatele

> Vytvoříme `login.php` soubor
> - login bude podobný jako register - můžeme zkopírovat
> - přidáme možnost pamatovat si hesla (předvyznačit)
> - změníme hodnoty inputů - parametry type a name
```php
	<?php include_once "_partials/header.php" ?>

	<form method="post" action="" class="box box-auth">
		<h2 class="box-auth-heading">
			Login
		</h2>

		<input type="email" value="<?php echo $_POST["username"] ?: "" ?>" class="form-control" name="username" placeholder="Email Address" required autofocus>
		<input type="password" class="form-control" name="password" placeholder="Password" required>
		<label class="checkbox">
			<input type="checkbox" value="remember-me" id="rememberMe" name="rememberMe" checked>Remember me
		</label>
		<button class="btn btn-lg btn-primary btn-block" type="submit">Login</button>

		<p class="alt-action text-center">
			or <a href="<?= BASE_URL ?>/login">come inside (of me)</a>
		</p>
	</form>

<?php include_once "_partials/footer.php" ?>
```

> Nyní budeme odchytávat login data
```php
// register from submitted
if ( $_SERVER["REQUEST_METHOD"] === "POST" )
{
    $username = filter_var( $_POST["username"], FILTER_SANITIZE_EMAIL );
    $password = $_POST["password"];
    $remember = $_POST["rememberMe"] ? true : false;
}
```
> vložíme kód přihlášení z githubu
> - upravíme login funkci na naše hodnoty, register odstraníme
> - při neúspěšném přihlášení vyhodíme error message knihovny
> - echo tam být nemusí - smažeme
> - v cookies `setcookie` je potřeba přepsat `$config` na naši proměnnou `$auth_config`
```php
$login = $auth->login($username, $password, $remember);

if( $login['error'] ) {
    // Something went wrong, display error message
    flash()->error($login['message']);
} else {
    // Logged in successfully, set cookie, display success message
    setcookie(
        $auth_config->cookie_name,
        $login['hash'],
        $login['expire'],
        $auth_config->cookie_path,
        $auth_config->cookie_domain,
        $auth_config->cookie_secure,
        $auth_config->cookie_http
    );
}
```

> aby se cookie zapsala, musí se provést redirect 
> - server řekne prohlížeči, že při dalším requestu má přibalit cookie informace
```php

flash()->success("sub bro");
redirect("/");
```

Nyní funguje registrace i přihlášení, avšak bylo by dobré zjistit, jestli tomu tak opravdu je
> v `header.php` necháme vypsat stav přihlášení (z Git dokumentace)
```php
// if false
if (!$auth->isLogged()) {
    echo "Forbidden";
}
// if true
else{
    echo "Logged in";
}
```
# 114 - Login, logout, get_user, pomocné funkce, strytí obsahu za loginem

můžeme si vytvořit naše funkce na odhlášení `do_logout()` a přihlášení `logged_in()`

> vytvoříme soubor `logout.php`
> - zkontrolujeme, jestli jsme logged in, pokud ne - redirect domů
> - pokud jsme - zavoláme funkci `do_logout()`
```php
	require_once '_inc/config.php';

	// you ain't even logged in, what are you doing
	if ( ! $auth->isLogged ) {
		redirect('/');
	}

	// log yourself out, bro
	do_logout();

	// flash it & go home
	flash()->success('Bye bye!');
	redirect('/');
```
Lepší bude pro podmínku se stavem přihlášení vytvořit funkci
> vytvoříme logged_in funkci v functions-auth.php
> - víme že funkce na získání stavu přihlášení je $auth->isLogged()
> - pokud nevíme co (jaký datový typ) vrací, můžeme si nechat vypsat
```php
function logged_in()
{
    global $auth;
    // returns true/false
    return $auth->isLogged();
}
```
Vytvoříme funkci na odhlášení
> vytvoříme do_logout funkci v functions-auth.php
> -  v `logout()` funkci knihovny vidíme, že logout metoda potřebuje jako parametr HASH - víme, že HASH skladujeme v cookie
```php
function do_logout()
{
    global $auth, $auth_config;

    // if true
    if ( logged_in() ){
        $logout = $auth->logout( $_COOKIE[$auth_config->cookie_name] );
    }
    return $logout;
}
```
> v `config.php` soubor necháme requirnout
```php
require_once "functions-auth.php";
```
Nyní můžeme přejít na získání identity o přihlášení
- máme pouze HASH v cookie
- při listování funkcí můžeme najít funkci `getSessionUID(hash)`, která dokáže získat ID podle HASH
> zkontrolujeme, co vznikne z této funkce, když do ní pošleme HASH
> - vyhodí nám id uživatele
```php
// hash
$hash = $_COOKIE[$auth_config->cookie_name];
// user id control
echo "<pre>";
print_r($auth->getSessionUID($hash));
echo "</pre>";
```
> ID si můžeme vložit do proměnné a zkusit získat data o uživateli
> - vyhodí data o uživateli
```php
// users data control
$user_id = $auth->getSessionUID($hash);

echo "<pre>";
print_r($auth->getUser($user_id));
echo "</pre>";
```
Z toho si můžeme vytvořit funkci `get_user` v `function-auth.php`
> Získáme ID a data uživatele
```php
function get_user(){
    
    $user_id = $auth->getSessionUID($_COOKIE[$auth_config->cookie_name]);
    
    return $auth->getUser($user_id);
}
```
> Jenže musíme myslet na to, že uživatel nemusí být přihlášen - funkce s tím musí počítat
> - přidáme parametr $user_id, který defaultně nastavíme na 0 a podmínku podle toho či je uživatel přihlášen
> - můžeme použít funkci `is_logged()` místo `$auth->isLogged()`
> - vrátí se pole - necháme castnout na objekt (lépe se k němu bude přistupovat)
```php
function get_user($user_id = 0)
{
    global $auth, $auth_config;

    if ( is_logged() )
    {
        $user_id = $auth->getSessionUID($_COOKIE[$auth_config->cookie_name]);
    }
    
    return (object) $auth->getUser($user_id);
}
```
> nyní v header.php můžeme použít funkce `is_logged()` jako stav přihlášení a `get_user()` jako informace o uživateli
> - mělo by vrátit stejná data jako předtím
```php
if ( is_logged() ) {
    // if true
    echo "Logged in";
   
    // user id control
    // echo "<pre>";
    // print_r($auth->getSessionUID($hash));
    // echo "</pre>";

    // echo "<pre>";
    // users data control
    // print_r(get_user());
    // function output control
    // global $auth;
    // print_r($auth->isLogged());
    // echo "</pre>";
}
else{
    // if false
    echo "Forbidden";
}
```
Nyní vytvoříme autorizaci
- nepřihlášený uživatel může jít pouze na login a registraci

> rozšíříme `index.php`, kde máme routy
> - přidáme pole veřejně přístupných adres a podmínku
```php
$public = [
    "login", "register"
];
// pokud nejsi přihlášen a chceš jít na stránku, která se nenachází ve veřějně přístupných adresách
if ( !logged_in() && !in_array($page, $public) )
{
    // vrátí tě na login
    redirect("/login");
}
```
> můžeme použít funkci logged_in v logout.php
```php
// you ain't even logged in, what are you doing
if ( !logged_in() ) {
    redirect('/');
}
```
Stále ale nemáme skrytou navigaci pro ty, co nejsou přihlášení
> přidáme podmínku pro navigaci v `header.php`
> - smažeme kontrolu o stavu přihlášení
```php
<?php if ( logged_in() ) : ?>
<div class="navigation btn-group btn-group-xd">
    <a href="<?php echo BASE_URL ?>" class="btn btn-default">home</a>
    <!-- link na vytvoření příspěvku -->
    <a href="<?php echo BASE_URL ?>/post/new" class="btn btn-default">add new</a>
</div>
<?php endif ?>
```
Nyní bychom chtěli logout link a email přihlášeného uživatele
> budeme potřebovat zjistit údaje o přihlášeném uživateli, abychom je mohli použít
```php
<?php if ( logged_in() ) : $logged_in = get_user() ?>
<div class="navigation">
    <div class="btn-group btn-group-sm pull-left">
        <a href="<?php echo BASE_URL ?>" class="btn btn-default">home</a>
        <!-- link na vytvoření příspěvku -->
        <a href="<?php echo BASE_URL ?>/post/new" class="btn btn-default">add new</a>
    </div>
    <div class="btn-group btn-group-sm pull-right">
        <span class="username small"><?php echo plain($logged_in->email) ?></span>
        <a href="<?php echo BASE_URL ?>/logout" class="btn-default logout">logout</a>
    </div>
</div>
<?php endif ?>
```

# 115 - Autorizace, příspěvky patří uživatelům, mají autory, KONEC

příspěvkům budeme přidávat user_id
- pro kontrolu přidáme každému příspěvku user_id (náhodně)

> ve `functions-post.php` funkci `create_post_query` přidáne do query tabulku `user`
> - aby vždy když vytahuji infomraci o příspěvku vytáhl i jeho post_id
> - zároveň v SELECTU vytáhnu email, který bude symbolizovat autora přříspěvku (bude to link - po kliknutí vyjedou jeho články)
> - nezapomenou, že tabulka users je pro nás tabulka knihovny (pokud nemáme přenastavené) - takže phpauth_users
```PHP
$query = "
    SELECT p.*, u.email, GROUP_CONCAT(t.tag SEPARATOR '-||-') AS tags
    FROM posts p
    LEFT JOIN posts_tags pt ON (p.id = pt.post_id)
    LEFT JOIN tags t ON  (t.id = pt.tag_id)
    LEFT JOIN phpauth_users u ON (p.user_id = u.id)
";
```
> ve `functions-post.php` funkci `format_post` vytažený email z databáze sanitiznu a vytvořím link
```php
// user
$post["email"] = filter_var( $post["email"], FILTER_SANITIZE_EMAIL );
$post["user_link"] = BASE_URL . "/user/" . $post["user_id"];
$post["user_link"] = filter_var($post["user_link"], FILTER_SANITIZE_URL);
```
> v `post.php` můžu přidat odstavec s autorem článku (přes který se proklikne na autora)
```php
<div class="post-content">
    <?php echo $post->text ?>
    <p class="written-by small">
        <small>- written by <a href="<?php echo $post->user_link ?>"><?php echo $post->email ?></a></small>
    </p>
</div>
```
kdybychom chtěli, tak jsou způsoby jak zakrýt email před googlem (před botmi)
- kdokoliv může naprogramovat robota, který sesbírává vše ze stránky co vypadá jako email - pak na něj posílat spam
- pokud chceme zašifovat, tak hledat pod názvem email obfuscation

Nyní musíme vytvořit routu pro emaily v URL
> vytvoříme v `index.php`
> - /user otevře `user.php` soubor
```php
// USER
"/user" => [
    "GET" => "user.php"
],
```
> vytvoříme user.php soubor - podobné jako tags (můžu si zkopírovat)
> - získáváme uživatele z 2. segmentu, můžeme použít `get_user()` funkci (získá id)
> - pošleme do funkce id usera / user id = uid (ne id - vytáhli bychom články přihlášeného)
```php
$user = get_user( segment(2) );

try{
    $results = get_posts_by_user( $user->uid );
}
```
> v nadpise se bude zobrazovat email usera
```php
<h1 class="box-heading text-muted"><small>by</small><?php echo plain( $user->email ) ?></h1>
```
> Vytvoříme funkci `get_posts_by_user()` v `functions-post.php` - bude podobné jako `get_posts_by_tag()`
> - budeme do ní posílat user_id (přednastavíme na 0)
> - podmínka - nebudeme hledat tag, ale user id WHERE post user id = uid
> - do uid si necháme poslat id usera
```php
function get_posts_by_user( $user_id = 0, $auto_format = true ){
    
    if( !$user_id && !$user_id = get_user()->uid ){
        return false;
    }

    global $db;

    $query = $db->prepare( create_posts_query( "WHERE p.user_id = :uid" ) );
    $query->execute(["uid" => $user_id ]);
}
```
Nyní zobrazuje příspěvky uživatelů, ale pokud zadáme do URL id které neexistuje zobrazí vše
> ve fukci `get_posts_by_user` upravíme podmínku
```php
if( !$user_id ){
    return [];
}
```
Vytvoříme link pro zobrazení našich (námi napsaných) příspěvků v navigaci
> v `header.php` vytvoříme link
> - víme, že funkce `loggeed_in()` vrací pole, kde máme id přihlášeného usera
```php
<a href="<?php echo BASE_URL ?>" class="btn btn-default">all posts</a>
<a href="<?php echo BASE_URL ?>/user/<?php echo $logged_in->uid ?>" class="btn btn-default">my posts</a>
```
Nyní chceme odstranit možnost upravit či odstrant článek, který jsem nenapsal já
> v `post.php` vytvoříme kontrolu pro zobrazení linků k odstranění či editaci
> - zobraz pokud příspěvek patří přihlášenému uživateli
```php
<?php if ( can_edit($post) ) : ?>
    <a href="<?php echo get_edit_link( $post ) ?>" class="btn btn-xd edit-link">
        edit
    </a>

    <a href="<?php echo get_delete_link( $post ) ?>" class="btn btn-xd edit-link">
        &times;
    </a>
<?php endif ?>
```
> Vytvoříme funkci `can_edit()` ve `functions-auth.php`
```php
function can_edit( $post )
{
    // must be logged in
    // pokud nejsem přihlášen - nemůžů editovat
    if (!logged_in()){
        return false;
    }
    
    // potřebuji zjistit id usera, který napsal tento post
    if (is_object($post)){
        // víme, že $post může být array nebo objekt (podle toho či je nebo není naformátovaný)
        $post_user_id = (int) $post->user_id;
    }
    else{
        $post_user_id = (int) $post["user_id"];
    }
    // zjistíme jaký user je přhlášený
    $user = get_user();
    // porovnáme usera, co napsal článek s přihlášeným userem - pokud ano, vrátí true (můžu editovat)
    return $post_user_id === $user->uid;
}
```
Nestačí pouze zobrazení linků, musím i kontrolovat práva (či můžu ho udělat) při samotné akci
- stále můžu zadat přes URL blog/edit/2
> v souboru `edit-item.php` v _admin musím provést kontrolu
```php
// is this the autor
if ( !can_edit($post)){
    flash()->error("what are you trying to pull here");
    redirect("back");
}
```
> přidáme kontrolu, zda příspěvek vůbec exituje (vyhneme se budoucím errorům)
```php
// does this even exist?
if ( !$post = get_post( $post_id, false ) ){
    flash()->error("no such post");
    redirect("back");
}
```
To jsme udělali pro edit, nyní ještě uděláme pro delete
- můžeme zkopírovat, co jsme vytvořili v editu a doupravíme
```php
// pokud nemáme post_id nebo post, kterého máme id vůbec neexistuje v databázi
if ( !$post_id || !$post = get_post( $post_id, false ) ){
    flash()->error("no such post");
    redirect("back");
}
// pokud ho máme, zkontrolujeme či ho může vymazat
if ( !can_edit($post)){
    flash()->error("what are you trying to pull here");
    redirect("back");
}
```
Potřebuji taky vložit user_id aktuálně přihlášeného usera (ten co příspěvek vytvořil)
> v `add-item.php` vkládám item do databáze pomocí $query
```php
$query = $db->prepare("
    INSERT INTO posts
        ( user_id, title, text, slug )
    VALUES
        ( :uid, :title, :text, :slug )
");

$insert = $query->execute([
    // vytáhenme si user id (jeho uid)
    'uid'     => get_user()->uid,
    'title'   => $title,
    'text'    => $text,
    'slug'    => $slug
]);
```

