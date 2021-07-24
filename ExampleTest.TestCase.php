<?php


######################################     PHPUNIT  TESTFILE        #################################### 


#Test.php FILE #PHPUNIT TEST
class ProvidersTest extends TestCase
{
    #tested
    public function testProviderAlbumCommentAdd()
    {
        $album = DB::table('providers_albums')->inRandomOrder()->first();
        
        $profile = $this->getProfileByUser();
        $user = $this->getUserByTable($profile->uid);
        
        
        //URL to be passed down
        $url = $this->dpath('/profiles/{profile_uid}/providers/{provider_uid}/albums/{album_uid}/comments', [
            'profile_uid' => $profile->uid,
            'provider_uid' => $album->provider_uid,
            'album_uid' => $album->uid
        ]);

        if ($this->withParams !== 'true') {
            $parameters = [];
        }

        //Parameters to be passed 
        $parameters = [
                'author_uid' => $profile->uid,
                'content' => 'test comment add',
                    ];

        $this->post($url, $parameters, $this->headers($user))
        ->seeStatusCode(200)
        ->seeJsonStructure([
            'data' => [
                "uid",
                "profile_uid",
                "provider_uid",
                "album_uid",
                "content",
            ]
        ]);
        // ->dump()
    }

    #add provider's aff click
    public function testProvidersAffiliateClickAdd()
    {
        //Selecting one random affiliates table
        $aff = DB::table('affiliates_stats_orders')->inRandomOrder()->first();

        //Selecting Profile based on table
        $profile = DB::table('profiles')->where('uid', $aff->profile_uid)->first();
        $user = $this->getUserByTable($profile);
        
        //URL to be passed down
        $url = $this->dpath(
            '/profiles/{profile_uid}/providers/affiliates/{aff_uid}/clicks',
            [   
                'profile_uid' => $profile->uid,
                'aff_uid'     => $aff->uid
            ]
        );

        //Parameters to be passed 
        $parameters = [
            'aff_uid'   => UID::generateUid()
        ];

        //POST Method
        $this->post($url, $parameters, $this->headers($user))
            ->seeStatusCode(200)
            ->seeJsonStructure([
                'data' => [
                    'profile_uid',
                    'stats_d',
                    'total',
                    'created_at',
                    'updated_at',
                ],
            ]);
            // ->dump()
    }

}

/**
 * ##################   TestCase.php     #################################################################
 */

#TestCase.PHP

class TestCase extends Laravel\Lumen\Testing\TestCase
{

    public $withParams = 'true';

    /**
     * Creates the application.
     *
     * @return \Laravel\Lumen\Application
     */
    public function createApplication()
    {
        return require __DIR__.'/../bootstrap/app.php';
    }

    /**
     * Main Methods
     */

    #transforms {var_uid} from $url into URL
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
     * GET RANDOM ***
     */
    
    
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

    /**
     * GET SPECIFICS
     */


    /**
     * User that belongs to $table
     * 
     * @param profile_attributes_table|uid_from_table
     * @param null returns a Random User
     * @example $uid is passed as the table which contains a profile_uid or user_uid field
     * @return User
     */
    protected function getUserByTable($uid = null)
    {
        //$uid is not passed, 0, FALSE, NULL or ''
        if(empty($uid)){
            return $this->getRandomAuthUser();
        }

        //$uid is passed as $profile
        if(isset($uid->user_uid)){        
            return User::where('uid', $uid->user_uid)->first();
        };

        //$uid is passed as main table ex. $cart WHERE EXISTS table.profile_uid
        if(isset($uid->profile_uid)){
            
            $profile = UserProfile::where('uid', $uid->profile_uid)->first();        
            return User::where('uid', $profile->user_uid)->first();
        };
        
        //$uid is passed as a string representing profile->uid 
        
        return User::where('uid', function ($query) use ($uid){
                $query->select('user_uid')
                      ->from('users_profiles')
                      ->where('uid', $uid);
                })->orWhere('uid', $uid)->first();
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
    


    /**
     *   Dump Method
     * --debug
     *  
     * @param string Output function can be print_r, var_dump or var_export
     * @param boolean $json_decode 
     */
    public function dump($json_decode = 1, $other = null)
    {
        $content = $this->response->getContent();
        $seperator = '=============================================================';

        //info receive array
        $info = [

            'uid',
            'user_uid',
            
               ];

        //todo
        if ($json_decode === 'headers') {
            
            #TODO HEADERS
            $profile = $this->getProfileByUser();
            $user = $this->getUserByProfile($profile);
            //TODO Dump headers-bearer token and any other info
            

            foreach ($profile as $key => $value) {
                if (in_array($key, $info)) {
                    $new[$key] = $value;
                }
            }
            var_export($info);

            var_export($this->headers($other));
        } else {
            
            if (isset($json_decode)) {
                $content = json_decode($content, true);
            }
            
            

            #TODO: REMOVE AND USE SWITCH //if value => 0
            if (!empty($json_decode)) {
                echo PHP_EOL . $seperator . "\n" . PHP_EOL;
                var_export($content);
                echo "\n" . $seperator . PHP_EOL;
            } else {
                echo PHP_EOL . $seperator . PHP_EOL;
                echo $content['status_code'].' >>> '.$content['error']['message']; # ?? $content['status_code']['message'] ?? "everthing is fine or no message";
                echo PHP_EOL . $seperator . PHP_EOL;
            }
            return $this;
        }
    }
    

    #get Random Things Function ###TODO more options


    protected function getRand($opt = null){
        
        return $this->randomEmail($opt);
    }
    #Random Email #Other Stuff
    protected function randomEmail($opt = null)
    {

        $host = [
            'gmail',
            'yahoo',
            'rds',
            'digiromania',
            'hotmail',
            'live',
            'outlook',
            'media',
            'samsung',
            'tester',
            'gooogleee',
            'bestbuy',
            'locopoco'
        ];
        // $name = $this->randomName();
        $code = [
            'ro', 'it', 'com', 'lk', 'de', 'fr', 'au', 'us', 'gov'
        ];
        
        switch ($opt) {
            case 'host':
                $value= $host[rand(0, count($host) - 1)];
                break;
            case 'name':
                $value= $this->randomName();
                break;
            case 'fname':
                $value= $this->randomName('first');
                break;
            case 'lname':
                $value= $this->randomName('last');
                break;
            case 'code':
                $value= $host[rand(0, count($host) - 1)];
                break;
            default:
                $email = $this->randomName();
                $email .= "@";
                $email .= $host[rand(0, count($host) - 1)];
                $email .= ".";
                $email .= $code[rand(0, count($code) - 1)];
                break;
        }    

        return $value;
    }


    #Random Name 
    protected function randomName($opt = null) {
        $firstname = array(
            'Johnathon',
            'Anthony',
            'Erasmo',
            'Raleigh',
            'Nancie',
            'Tama',
            'Camellia',
            'Augustine',
            'Christeen',
            'Luz',
            'Diego',
            'Lyndia',
            'Thomas',
            'Georgianna',
            'Leigha',
            'Alejandro',
            'Marquis',
            'Joan',
            'Stephania',
            'Elroy',
            'Zonia',
            'Buffy',
            'Sharie',
            'Blythe',
            'Gaylene',
            'Elida',
            'Randy',
            'Margarete',
            'Margarett',
            'Dion',
            'Tomi',
            'Arden',
            'Clora',
            'Laine',
            'Becki',
            'Margherita',
            'Bong',
            'Jeanice',
            'Qiana',
            'Lawanda',
            'Rebecka',
            'Maribel',
            'Tami',
            'Yuri',
            'Michele',
            'Rubi',
            'Larisa',
            'Lloyd',
            'Tyisha',
            'Samatha',
        );
    
        $lastname = array(
            'Mischke',
            'Serna',
            'Pingree',
            'Mcnaught',
            'Pepper',
            'Schildgen',
            'Mongold',
            'Wrona',
            'Geddes',
            'Lanz',
            'Fetzer',
            'Schroeder',
            'Block',
            'Mayoral',
            'Fleishman',
            'Roberie',
            'Latson',
            'Lupo',
            'Motsinger',
            'Drews',
            'Coby',
            'Redner',
            'Culton',
            'Howe',
            'Stoval',
            'Michaud',
            'Mote',
            'Menjivar',
            'Wiers',
            'Paris',
            'Grisby',
            'Noren',
            'Damron',
            'Kazmierczak',
            'Haslett',
            'Guillemette',
            'Buresh',
            'Center',
            'Kucera',
            'Catt',
            'Badon',
            'Grumbles',
            'Antes',
            'Byron',
            'Volkman',
            'Klemp',
            'Pekar',
            'Pecora',
            'Schewe',
            'Ramage',
        );

        if ($opt === 'first') {
            return $firstname[rand ( 0 , count($firstname) -1)];
        }
        if ($opt === 'last') {
            return $lastname[rand ( 0 , count($lastname) -1)];
        }

        $name = $firstname[rand ( 0 , count($firstname) -1)];
        $name .= ' ';
        $name .= $lastname[rand ( 0 , count($lastname) -1)];
    
        return $name;
    }



}
