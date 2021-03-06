<?php
/**
 * Global vitero settings
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id: class.ilViteroSettings.php 36242 2012-08-15 13:00:21Z smeyer $
 */
class ilViteroSettings
{
	private static $instance = null;
	
	private $storage = null;
	
	private $url = 'http://yourserver.de/vitero/services';
	private $webstart = 'http://yourserver.de/vitero/start.htm';
	private $admin = '';
	private $pass = '';

	private $customer = NULL;
	private $use_ldap = false;
	private $enable_cafe = false;
	private $enable_content = false;
	private $enable_standard_room = true;
	private $user_prefix = 'il_';
	private $avatar = 0;
	private $mtom_cert = '';

	private $grace_period_before = 15;
	private $grace_period_after = 15;

	
	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->storage = new ilSetting('vitero_config');
		$this->read();
	}
	
	/**
	 * Get singelton instance
	 * 
	 * @return ilViteroSettings
	 */
	public static function getInstance()
	{
		if(self::$instance)
		{
			return self::$instance;
		}
		return self::$instance = new ilViteroSettings();
	}

	/**
	 * Get storage
	 * @return ilSetting
	 */
	public function getStorage()
	{
		return $this->storage;
	}
	
	public function getServerUrl()
	{
		return $this->url;
	}
	
	public function setServerUrl($a_url)
	{
		$this->url = $a_url;
	}

	/**
	 * Get direct link to group managment
	 */
	public function getGroupFolderLink()
	{
		$group_url = str_replace('services', '', $this->getServerUrl());
		$group_url = ilUtil::removeTrailingPathSeparators($group_url);
		return $group_url.'/user/cms/groupfolder.htm';
	}

	public function setWebstartUrl($a_url)
	{
		$this->webstart = $a_url;
	}

	public function getWebstartUrl()
	{
		return $this->webstart;
	}
	
	public function setAdminUser($a_admin)
	{
		$this->admin = $a_admin;
	}
	
	public function getAdminUser()
	{
		return $this->admin;
	}
	
	public function setAdminPass($a_pass)
	{
		$this->pass = $a_pass;
	}
	
	public function getAdminPass()
	{
		return $this->pass;
	}

	public function setCustomer($a_cust)
	{
		$this->customer = $a_cust;
	}

	public function getCustomer()
	{
		return $this->customer;
	}

	public function useLdap($a_stat)
	{
		$this->use_ldap = $a_stat;
	}

	public function isLdapUsed()
	{
		return $this->use_ldap;
	}

	public function enableCafe($a_stat)
	{
		$this->enable_cafe = $a_stat;
	}

	public function isCafeEnabled()
	{
		return $this->enable_cafe;
	}

	public function enableStandardRoom($a_stat)
	{
		$this->enable_standard_room = $a_stat;
	}

	public function isStandardRoomEnabled()
	{
		return $this->enable_standard_room;
	}
	
	public function enableContentAdministration($a_stat)
	{
		$this->enable_content = $a_stat;
	}
	
	public function isContentAdministrationEnabled()
	{
		return $this->enable_content;
	}

	public function setUserPrefix($a_prefix)
	{
		$this->user_prefix = $a_prefix;
	}

	public function getUserPrefix()
	{
		return $this->user_prefix;
	}

	public function setStandardGracePeriodBefore($a_val)
	{
		$this->grace_period_before = $a_val;
	}

	public function getStandardGracePeriodBefore()
	{
		return $this->grace_period_before;
	}

	public function setStandardGracePeriodAfter($a_val)
	{
		$this->grace_period_after = $a_val;
	}

	public function getStandardGracePeriodAfter()
	{
		return $this->grace_period_after;
	}

	public function enableAvatar($a_stat)
	{
		$this->avatar = $a_stat;
	}

	public function isAvatarEnabled()
	{
		return (bool) $this->avatar;
	}
	
	public function setMTOMCert($a_cert)
	{
		$this->mtom_cert = $a_cert;
	}
	
	public function getMTOMCert()
	{
		return $this->mtom_cert;
	}


	public function save()
	{
		$this->getStorage()->set('server', $this->getServerUrl());
		$this->getStorage()->set('admin', $this->getAdminUser());
		$this->getStorage()->set('pass', $this->getAdminPass());
		$this->getStorage()->set('customer', $this->getCustomer());
		$this->getStorage()->set('ldap', $this->isLdapUsed());
		$this->getStorage()->set('cafe', $this->isCafeEnabled());
		$this->getStorage()->set('content',$this->isContentAdministrationEnabled());
		$this->getStorage()->set('std_room',(int) $this->isStandardRoomEnabled());
		$this->getStorage()->set('webstart',$this->getWebstartUrl());
		$this->getStorage()->set('uprefix',$this->getUserPrefix());
		$this->getStorage()->set('grace_period_before',$this->getStandardGracePeriodBefore());
		$this->getStorage()->set('grace_period_after',$this->getStandardGracePeriodAfter());
		$this->getStorage()->set('avatar',(int) $this->isAvatarEnabled());
		$this->getStorage()->set('mtom_cert',$this->getMTOMCert());
	}
	
	protected function read()
	{
		$this->setServerUrl($this->getStorage()->get('server', $this->url));
		$this->setAdminUser($this->getStorage()->get('admin', $this->admin));
		$this->setAdminPass($this->getStorage()->get('pass', $this->pass));
		$this->setCustomer($this->getStorage()->get('customer', $this->customer));
		$this->useLdap($this->getStorage()->get('ldap', $this->use_ldap));
		$this->enableCafe($this->getStorage()->get('cafe', $this->enable_cafe));
		$this->enableContentAdministration($this->getStorage()->get('content'),$this->enable_content);
		$this->enableStandardRoom($this->getStorage()->get('std_room', $this->enable_standard_room));
		$this->setWebstartUrl($this->getStorage()->get('webstart',$this->webstart));
		$this->setUserPrefix($this->getStorage()->get('uprefix',$this->user_prefix));
		$this->setStandardGracePeriodBefore($this->getStorage()->get('grace_period_before',$this->grace_period_before));
		$this->setStandardGracePeriodAfter($this->getStorage()->get('grace_period_after', $this->grace_period_after));
		$this->enableAvatar($this->getStorage()->get('avatar', $this->avatar));
		$this->setMTOMCert($this->getStorage()->get('mtom_cert',$this->mtom_cert));
	}
}
?>
