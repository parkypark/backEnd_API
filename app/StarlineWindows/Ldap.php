<?php namespace StarlineWindows;

require_once(dirname(__FILE__).'/../../vendor/adLDAP/lib/adLDAP/adLDAP.php');

use adLDAP\adLDAP;

class StarlineLdap {

    private static $ldapConfig1 = [
        'account_suffix'        => '@strarc.starlinewindows.com',
        'base_dn'               => 'DC=strarc,DC=starlinewindows,DC=com',
        'domain_controllers'    => ['beta.strarc.starlinewindows.com'],
        'use_ssl'               => false,
        'use_tls'               => false
    ];

    private static $ldapConfig2 = [
        'account_suffix'        => '@starlinewindows.com',
        'base_dn'               => 'DC=starlinewindows,DC=com',
        'domain_controllers'    => ['192.168.110.3'],
        'use_ssl'               => false,
        'use_tls'               => false
    ];

    public static function getConfig()
    {
        return self::$ldapConfig2;
    }

    /**
     * Attempts to authenticate user via configured ldap server.
     *   success = array of user info
     *   failure = false
     *
     * @param $username
     * @param $password
     * @return array|bool
     */
    public static function authenticate($username, $password)
    {
        try
        {
            $adLdap = new adLDAP(self::$ldapConfig1);
            if (! $adLdap->user()->authenticate($username, $password))
            {
              $adLdap = new adLDAP(self::$ldapConfig2);
              if (! $adLdap->user()->authenticate($username, $password))
              {
                return false;
              }
            }

            $userInfo = $adLdap->user()->info($username);
            return [
                'full_name' => $userInfo[0]['displayname']['0'],
                'email'     => isset($userInfo[0]['mail']) ? $userInfo[0]['mail']['0'] : '',
                'member_of' => self::getGroups($userInfo[0]['memberof'])
            ];
        }
        catch (Exception $e)
        {
            Log::error($e);
            return false;
        }
    }

    private static function getGroups($data)
    {
        $ret = [];

        foreach ($data as $groupData)
        {
            if (strpos($groupData, 'CN=') === false)
            {
                continue;
            }

            $ret[] = explode('=', explode(',', $groupData)[0])[1];
        }

        sort($ret);
        return $ret;
    }
}
