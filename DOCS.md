php -S 0.0.0.0:8000 -t public ./public/index.php

# FastRoute

https://github.com/nikic/FastRoute

# Whoops

http://filp.github.io/whoops/

# DB

https://github.com/paragonie/easydb

? https://doc.nette.org/en/3.0/database-core
? https://doc.nette.org/en/3.0/database-explorer

# Mailer

https://doc.nette.org/en/3.0/mailing

Temp mails service: https://temp-mail.org/ru/

# CSRF

https://github.com/paragonie/anti-csrf

```
if($_POST && (new AntiCSRF())->validateRequest()) {
    // valid
} else {
    // invalid
}

//------------------

<form action="/" method="post">
    <?php echo $this->csrf('/') ?>
    <input type="text" name="login">
    <input type="submit">
</form>
```

? https://doc.nette.org/en/3.0/forms

# Input validation

https://respect-validation.readthedocs.io/en/1.1/concrete-api/
https://github.com/acurrieclark/php-password-verifier

# Captcha

https://github.com/Gregwar/Captcha

# Plates

http://platesphp.com/v3/

Using the asset extension
```
<link rel="stylesheet" href="<?=$this->asset('/css/all.css')?>" />
<img src="<?=$this->asset('/img/logo.png')?>">
```

-------------------

URI extension
```
<ul>
    <li <?=$this->uri('/', 'class="selected"')?>><a href="/">Home</a></li>
    <li <?=$this->uri('/about', 'class="selected"')?>><a href="/about">About</a></li>
    <li <?=$this->uri('/products', 'class="selected"')?>><a href="/products">Products</a></li>
    <li <?=$this->uri('/contact', 'class="selected"')?>><a href="/contact">Contact</a></li>
</ul>
```
More examples by link: http://platesphp.com/v3/extensions/uri/