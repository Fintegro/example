<?php

namespace app\controllers;

use Yii;
use app\models\Token;

class UserBaseController extends BaseController
{   
    public function beforeAction($action)
    {
        parent::beforeAction($action);

        if($action->id != 'options'){
            if (!empty(Yii::$app->request->headers['bearer'])){
                $token = Token::findOne([
                    'token' => Yii::$app->request->headers['bearer']
                ]);
                if ($this->tokenValid($token)){
                    Yii::$app->user->setIdentity($token->user);
                    return true;
                }
            }
            
            Yii::$app->response->statusCode = 403;
        }
    }
    
    public function tokenValid($token)
    {
        if ($token){
            if ($token->expired > time()){
                return true;
            }

            $token->delete();
        }
    }
}
