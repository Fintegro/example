<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user".
 *
 * @property integer $id
 * @property string $login
 * @property string $password
 * @property string $ip
 * @property string $created
 * @property integer $deleted
 *
 * @property Photos[] $photos
 * @property Posts[] $posts
 * @property Profile[] $profiles
 * @property UserRelation[] $userRelation
 */
class User extends \yii\db\ActiveRecord implements \yii\web\IdentityInterface
{
    public $authKey;
    
    const TYPE_PASS = 1;
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user}}';
    }
    
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['login', 'password', 'email'], 'required'],
            [['login', 'email'], 'unique'],
            [['created'], 'safe'],
            [['deleted'], 'integer'],
            [['login', 'ip', 'password'], 'string', 'max' => 255],
            [['password'], 'string', 'min' => 6],
        ];
    }

    public function beforeSave($insert)
    {
        parent::beforeSave($insert);
        $this->password = Yii::$app->getSecurity()->generatePasswordHash($this->password);
        return true;
    }
    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'login' => 'Login',
            'password' => 'Password',
            'ip' => 'Ip',
            'email' => 'Email',
            'created' => 'Created',
            'deleted' => 'Deleted',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPosts()
    {
        return $this->hasMany(Posts::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getProfile()
    {
        return $this->hasOne(Profile::className(), ['user_id' => 'id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUserRelation()
    {
        return $this->hasMany(UserRelation::className(), ['user_id' => 'id']);
    }
    
    public function getUserFriends()
    {
        return $this->hasMany(UserRelation::className(), ['user_id' => 'id'])
            ->where(['type' => UserRelation::TYPE_FRIEND]);
    }
    
    public function getUserEnemies()
    {
        return $this->hasMany(UserRelation::className(), ['user_id' => 'id'])
            ->where(['type' => UserRelation::TYPE_ENEMY]);
    }
    
    public function getEnemyByUserId($id)
    {
        return $this->hasOne(UserRelation::className(), ['user_id' => 'id'])
            ->where([
                'type' => UserRelation::TYPE_ENEMY,
                'user_id' => $this->id,
                'related_user_id' => $id
            ])->one();
    }
    

    public static function findIdentity($id)
    {
        return self::findOne(['id' => $id, 'deleted' => null]);
    }

    /**
     * Not used, but required by \yii\web\IdentityInterface
     * 
     * @param type $token
     * @param type $type
     * @return type
     */
    public static function findIdentityByAccessToken($token, $type = null)
    {
        return null;
    }

    /**
     * Finds user by email
     *
     * @param  string      $email
     * @return User|null
     */
    public static function findByEmail($email)
    {
        return self::findOne(['email' => $email, 'deleted' => null]);
    }

    /**
     * Finds user by username
     *
     * @param string $username
     * @return static|null
     */
    public static function findByUsername($username)
    {
        return self::findOne(['login' => $username, 'deleted' => null]);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getAuthKey()
    {
        return $this->authKey;
    }

    /**
     * @inheritdoc
     */
    public function validateAuthKey($authKey)
    {
        return $this->authKey === $authKey;
    }

    /**
     * Validates password
     *
     * @param string $password password to validate
     * @return bool if password provided is valid for current user
     */
    public function validatePassword($password)
    {
        return Yii::$app->getSecurity()->validatePassword($password, $this->password);
    }
    
    /**
     * Create new user
     * 
     * @param array $data user`s data
     * @return boolean
     */
    public function register($data)
    {
        $this->login = $this->getParam($data, 'login');
        $this->email = $this->getParam($data, 'email');
        $this->password = $this->getParam($data, 'password');
        $this->ip = filter_input(INPUT_SERVER, 'REMOTE_ADDR');
        $this->created = date('Y-m-d H:i:s');
        if ($this->save()){
            $profile = new Profile;
            $profile->user_id = $this->id;
            $profile->firstname = $this->getParam($data, 'firstname');
            $profile->lastname  = $this->getParam($data, 'lastname');
            if ($profile->save()){
                return true;
            } else {
                $this->delete();
                $this->addProfileErrors($profile);
            }
        }
        
        return false;
    }
    
    /**
     * Add errors to User if profile can`t be created
     * @param \app\models\Profile $profile
     */
    protected function addProfileErrors(Profile $profile){
        foreach ($profile->getErrors() as $attr => $error){
            $this->addError($attr, $error);
        }
    }
    
    /**
     * Filtering users input
     * 
     * @param array $data users input
     * @param string $param required param
     * @return string
     */
    public function getParam($data, $param)
    {
        return isset($data[$param]) ? $data[$param] : null;
    }
    
    public function getFriends()
    {
        return $this->hasMany(Profile::className(), ['user_id' => 'related_user_id'])
            ->via('userFriends');
    }
    
    public function getEnemies()
    {
        return $this->hasMany(Profile::className(), ['user_id' => 'related_user_id'])
            ->via('userEnemies');
    }    
}
