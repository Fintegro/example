<?php

namespace app\controllers;

use Yii;
use app\models\Profile;

class ProfileController extends UserBaseController
{   
    public function actionIndex()
    {
        return [
            'profile' => Yii::$app->user->identity->profile,
            'friends' => Yii::$app->user->identity->friends,
            'enemies' => Yii::$app->user->identity->enemies,
        ];
    }
    
    public function actionView($id)
    {
        $profile = Profile::findOne(['user_id' => $id]);
        if ($profile && $profile->notBannedYou()){
            return [
                'profile' => $profile,
                'friends' => $profile->user->friends,
                'enemies' => $profile->user->enemies,                
            ];
        } 
        
        Yii::$app->response->statusCode = 404;
        return ['error' => 'Not found'];            
    }
    
    public function actionUpdate($id)
    {
        if ($id == Yii::$app->user->identity->id){
            $post = [];
            $post['Profile'] = Yii::$app->request->post();
            $profile = Yii::$app->user->identity->profile;
            $profile->load($post);

            if ($profile->save()){
                return ['status' => 1];
            } else {
                Yii::$app->response->statusCode = 400;
                return ['errors' => $profile->getErrors()];  
            }
        }
        
        Yii::$app->response->statusCode = 400;
        return ['error' => 'Bad request'];          
    }
    
    public function actionDelete($id)
    {
        if ($id == Yii::$app->user->identity->id){
            $user = Yii::$app->user->identity;
            $user->deleted = 1;
            $user->save();
        }
        
        Yii::$app->response->statusCode = 204;
    }    
}
