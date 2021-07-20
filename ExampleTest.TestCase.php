<?php


#Test.php FILE #PHPUNIT TEST
class Test extends TestCase
{
    #tested
    public function testProfileProviderAlbumCommentAdd()
    {
        $album = DB::table('providers_albums')->inRandomOrder()->first();
        
        $profile = $this->getProfileByUser();
        $user = $this->getUserByTable($profile->uid);

        $url = $this->dpath('/profiles/{profile_uid}/providers/{provider_uid}/albums/{album_uid}/comments', [
            'profile_uid' => $profile->uid,
            'provider_uid' => $album->provider_uid,
            'album_uid' => $album->uid
        ]);

        if ($this->withParams !== 'true') {
            $parameters = [];
        }
        $parameters = [
                'author_uid' => $profile->uid,
                'content' => 'test comment add',
                    ];

        $this->post($url, $parameters, $this->headers($user))
        ->seeStatusCode(200)
        // ->dump()
        ->seeJsonStructure([
            'data' => [
                "uid",
                "profile_uid",
                "provider_uid",
                "album_uid",
                "content",
            ]
        ]);
    }
}

#TestCase.PHP

class TestCase extends Laravel\Lumen\Testing\TestCase
{

    public $withParams = 'true';
    private $profiles =['Use without using Eloquent for each test'];

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    #transforms {var} from string into URL
    public function dpath($url, $parameters) 
    {
        $params = [];
        foreach($parameters  as $name => $value) {
            $params['{'.$name.'}'] = $value;
        }

        return str_replace(array_keys($params), array_values($params), $url);
    }
    

    #Main Func, User, Profile and Headers

    /**
     * Return request headers needed to interact with the API.
     *
     * @return Array array of headers.
     */
    protected function headers($user = null)
    {
        $headers = ['Accept' => 'application/json'];

        if (!is_null($user)) {
            $token = JWTAuth::fromUser($user);
            JWTAuth::setToken($token);
            $headers['Authorization'] = 'Bearer '.$token;
        }

        return $headers;
    }


    /**
     * Random user where table has profile_uid
     * @param uid $profile
     * @param uid $profile->uid
     * @return User
     */
    protected function getUserByTable($uid = null)
    {
        //$uid is not passed, 0, FALSE, NULL or ''
        if(empty($uid)){
            return User::inRandomOrder()->first();
        }

        //$uid is passed as $profile
        if(isset($uid->user_uid)){        
            return User::where('uid', $uid->user_uid)->first();
        };

        //$uid is passed as main object ex. $cart WHERE EXISTS cart.profile_uid
        if(isset($uid->profile_uid)){
            $profile = UserProfile::where('uid', $uid->profile_uid)->first();        
            return User::where('uid', $profile->user_uid)->first();
        };

        //$uis is passed as a string representing profile->uid 
        $profile = UserProfile::where('uid', $uid)->first();
        return User::where('uid', $profile->user_uid)->first();
    }




    /**
     * Returns User Profile
     * By User
     * @param null returns RandomProfile
     * @param user.uid|user
     * @return UserProfile
     */   
    protected function getProfileByUser($user = null)
    {   
        if (!$user) {
            return $this->getRandomProfile();
        };
        if($user->uid){    
        $uid = $this->getProfileUidByUser($user);
        
        return UserProfile::where('uid', $uid)->first();
        };
        return UserProfile::where('uid', $user)->first();
    }
    

    #Others
    /**
     * Dump Func shows JSON response
     * 
     *  
     * @param string Output function can be print_r, var_dump or var_export
     * @param boolean $json_decode 
     */
    protected function dump($json_decode = 1, $other = null)
    {

        $content = $this->response->getContent();
        $seperator = '===============================================================================';

        //What info would you like to receive?
        $info = [  

            'uid',
            'user_uid',
            
               ];

        //skip // not done yet //todo #shows bearer token and other info
        if ($json_decode === 'headers') {
            
            $profile = $this->getProfileByUser();
            $user = $this->getUserByProfile($profile);                   

            foreach ($profile as $key => $value) {
                if (in_array($key, $info)) {
                $new[$key] = $value;
                }
            }            
            
            var_export($info);
            var_export($this->headers($other));

        } else {

            #normal dump
            if (isset($json_decode)) {
                $content = json_decode($content, true);
            }

            if (!empty($json_decode)) {
                echo PHP_EOL . $seperator . "\n" . PHP_EOL;
                var_export($content);
                echo "\n" . $seperator . PHP_EOL;
            } 

            #sends only code and content/message
            else {
                echo PHP_EOL . $seperator . "\n" . PHP_EOL;
                var_export([$content['status_code'] ?? 'empty' => $content['error']  ?? $content['message'] ?? 'everthing is fine or no message']);
                echo "\n" . $seperator . PHP_EOL;
            }
        }
        return $this;

    }


    
    ##Random Functions
    protected function getRandomAuthUser()
    {
        $email = DB::table('users')->inRandomOrder()->first();
        return User::where('email', $email->email)->first();
    }
    
    protected function getRandomProfileUid()
    {
        $profile = DB::table('users_profiles')->inRandomOrder()->first();
        return $profile->uid;
    }

    protected function getRandomProfile()
    {
        return DB::table('users_profiles')->inRandomOrder()->first() ?? null;
    }



}
