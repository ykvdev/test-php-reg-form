<?php declare(strict_types=1);

namespace app\web\controllers;

use Gregwar\Captcha\CaptchaBuilder;

class UserController extends AbstractController
{
    public function registerAction() : void
    {
//        $builder = new CaptchaBuilder();
//        if($builder->testPhrase($userInput)) {
//            // instructions if user phrase is good
//        }
//        else {
//            // user phrase is wrong
//        }

        $this->renderView('user/register');
    }

    public function captchaAction() : void
    {
        $this->services->captcha()->buildAndOutput();
        exit();
    }
}