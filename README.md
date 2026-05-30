# ReclamDesign Modern V2 - proiect reparat

Proiect Laravel pentru magazin de produse personalizate: **Cancelarie**, **Haine**, **Banere**, **Cani**, coș, comenzi, emailuri HTML și panou **Admin**.

## Ce a fost reparat / adaugat

- Coșul funcționează de pe carduri și de pe pagina produsului.
- Coșul se salvează în `localStorage`, ca să poată păstra și imagini personalizate mari.
- Butonul **Despre noi** deschide pagina `/despre-noi`, nu mesaj/modal.
- Login admin: `Lord / chac`.
- Acces admin prin `/catadmin`; dacă se ajunge la o adresă de forma `/orice/pagina/catadmin`, aplicația redirecționează la `/catadmin`.
- La 5 parole greșite la admin apare GIF-ul `public/assets/fără_success/unsuccess.gif` pentru 10 secunde. Blocarea rămâne și după refresh, cât timp sesiunea este aceeași.
- Catalog cu categorii și subcategorii; la hover pe categorie apar subcategoriile în dreapta.
- Pagina `/catalog/{categorie}` afișează toate produsele categoriei și subcategoriile sub titlu. Dacă alegi o subcategorie, aceasta apare evidențiată cu bold/stil selectat.
- `Tricouri` este subcategorie a categoriei `Haine`.
- Admin poate crea subcategorii și poate alege caracteristicile lor: mărime, culoare, tip, dimensiuni, volum, design, față/spate, cană 3D.
- Admin poate adăuga produse în categorie + subcategorie. În formular apar doar caracteristicile relevante categoriei/subcategoriei alese.
- Admin poate vedea, adăuga, activa și dezactiva promocoduri.
- Promocod inclus: `CUDESCHIDERE`, reducere `10%`.
- Coșul are bloc nou **Promocode / Code** și aplică reducerea la total.
- La comandă se validează câmpurile Nume, Telefon și Email cu roșu/verde și mesaj clar.
- Avertizările dispar automat după 5 secunde și au linie animată de timp.
- În dark mode, cardurile produselor au umbră gri-albă la hover.
- În modul admin, pe cardurile produselor din pagina publică apar iconițele de modificare și ștergere.
- Headerul de sus cu **Despre noi** și **Contacte** rămâne doar sus, nu se mai lipește când derulezi pagina.
- Textele cerute au fost scoase din coș, footer și panoul admin.
- Emailurile de comandă includ opțiunile alese, reducerea, imaginile de design și atașează imaginile de design pentru client și companie.

## Personalizare produse

### Haine / subcategorii cu Față-Spate

Pe tricouri, hudi și orice subcategorie setată cu caracteristica **Față/Spate**:

- clientul poate alege mărimea și culoarea dacă produsul are aceste câmpuri;
- poate pune maximum 4 imagini;
- fiecare imagine poate fi selectată cu un click, mutată și mărită;
- butonul **Spate** întoarce produsul pe spate și apoi devine **Față**;
- pozițiile imaginilor se păstrează separat pe Față și pe Spate;
- apare criteriul `Modificat numai Față`, `Modificat numai Spate` sau `Modificat Față - Spate`;
- fiecare imagine adaugă `15 MDL` la prețul produsului;
- imaginea finală se salvează în coș, în comanda din baza de date și în email.

### Cani

Produsele din categoria `Cani` păstrează viewerul 3D. Textura încărcată se salvează în coș, comandă și email. Viewerul 3D folosește Three.js din CDN.

## Cerinte pentru instalare

- PHP `8.2` sau mai nou.
- Composer.
- MySQL sau MariaDB.
- Extensii PHP uzuale pentru Laravel: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`.
- Pentru upload și salvarea imaginilor personalizate, în `php.ini` este recomandat:
  - `post_max_size=20M` sau mai mult;
  - `upload_max_filesize=8M` sau mai mult.
- Pentru email real în Gmail/inbox, trebuie configurat SMTP. Implicit emailurile merg în log dacă `MAIL_MAILER=log`.

## Instalare de la zero

1. Dezarhivează proiectul.

2. Intră în folder:

```bash
cd ReclamDesignModern_V2
```

3. Instalează dependențele:

```bash
composer install
```

4. Creează `.env`:

```bash
cp .env.example .env
```

5. Generează cheia aplicației:

```bash
php artisan key:generate
```

6. Creează baza de date:

```sql
CREATE DATABASE ReclamDesignModern CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

7. Verifică setările din `.env`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ReclamDesignModern
DB_USERNAME=root
DB_PASSWORD=
```

8. Rulează migrările și seederele:

```bash
php artisan migrate:fresh --seed
```

9. Creează linkul pentru imaginile încărcate din admin:

```bash
php artisan storage:link
```

10. Curăță cache-ul:

```bash
php artisan optimize:clear
```

11. Pornește serverul local:

```bash
php artisan serve
```

Site:

```text
http://127.0.0.1:8000
```

Admin:

```text
http://127.0.0.1:8000/catadmin
```

Login admin:

```text
Nume: Lord
Parola: chac
```

## Instalare peste o bază de date existentă

Dacă ai deja baza de date cu proiectul vechi, rulează:

```bash
composer install
php artisan migrate
php artisan db:seed
php artisan storage:link
php artisan optimize:clear
```

Migrarea nouă adaugă subcategorii, promocoduri și câmpuri de reducere la comenzi. Pentru date curate de test, folosește `migrate:fresh --seed`.

## Configurare email

Implicit:

```env
MAIL_MAILER=log
```

Cu această setare emailurile se scriu în:

```text
storage/logs/laravel.log
```

Pentru trimitere reală prin SMTP, schimbă `.env`, de exemplu:

```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=utilizator@example.com
MAIL_PASSWORD=parola
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=tronciu.adrian@elev.cihcahul.md
MAIL_FROM_NAME="ReclamDesign Modern"
MAIL_COMPANY_ADDRESS=tronciu.adrian@elev.cihcahul.md
```

După plasarea unei comenzi, aplicația trimite:

- confirmare către client;
- notificare către companie;
- imaginile personalizate inline în HTML și ca atașamente, ca să fie vizibile și în Gmail.

## Structură importantă

```text
app/Http/Controllers      logica pagini, coș, comenzi și admin
app/Mail                  emailuri HTML și atașamente design
app/Models                modele Eloquent
app/Models/Subcategory.php subcategorii dinamice
app/Models/Promocode.php  promocoduri active/inactive
database/migrations       tabele și reparări bază de date
database/seeders          categorii, subcategorii, produse demo, admin, promocod
public/css/app.css        design responsive + dark/light mode
public/js/app.js          catalog, cautare instant, coș, promocod, personalizare față/spate
public/assets/fără_success/unsuccess.gif  GIF blocare admin
public/model/cana         modele 3D cană
resources/views           pagini Blade
```

## Comenzi utile

Curăță cache-ul:

```bash
php artisan optimize:clear
```

Rulează seederele:

```bash
php artisan db:seed
```

Reset complet baza de date:

```bash
php artisan migrate:fresh --seed
```

## Observații

- Folderul `vendor/` nu este inclus în arhivă; se instalează cu `composer install`.
- Pentru viewerul 3D la căni este necesar acces la CDN-ul Three.js sau trebuie copiat Three.js local și schimbat import map-ul din `resources/views/products/show.blade.php`.
- Pentru imaginile personalizate mari, coșul folosește `localStorage`; dacă browserul blochează localStorage sau spațiul este plin, imaginile pot să nu fie păstrate.
