<?php

defined('HOSTCMS') || exit('HostCMS: access denied.');

/**
 * Database Sessions
 *
 * @package HostCMS
 * @subpackage Core
 * @version 7.x
 * @copyright © 2005-2026, https://www.hostcms.ru
 */
class Core_Session_Database implements SessionHandlerInterface
{
	/**
	 * DataBase instance
	 * @var Core_DataBase
	 */
	protected $_dataBase = NULL;

	/**
	 * Session has been read
	 * @var boolean
	 */
	protected $_read = FALSE;

	/**
	 * Lock prefix
	 * @var string|NULL
	 */
	protected $_lockPrefix = NULL;

	/**
     * Currently locked session ID
     * @var string|NULL
     */
    protected $_currentLockId = NULL;
    
    /**
     * Time when lock was acquired
     * @var int|NULL
     */
    protected $_currentLockTime = NULL;

	/**
	 * GET_LOCK timeout (sec)
	 * @var int
	 */
	protected $_getLockTimeout = 5;

	/**
	 * Next step delay (microseconds)
	 * Default 0,5 sec - 500000 microseconds
	 * @var int
	 */
	protected $_nextStepDelay = 500000;

	/**
	 * Lock timeout
	 * @var int
	 */
	protected $_lockTimeout = 10;

	/**
	 * Constructor.
	 */
	public function __construct()
	{
		$this->_dataBase = Core_DataBase::instance();

		if (is_null($this->_lockPrefix))
		{
			$aDataBaseConfig = $this->_dataBase->getConfig();
			$this->_lockPrefix = $aDataBaseConfig['database'] . '_' . 'sessions';
		}
	}

	/**
	 * The open callback works like a constructor in classes and is executed when the session is being opened.
	 * @param string $path save path
	 * @param string $name session name
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function open($path, $name)
	{
		return TRUE;
	}

	/**
	 * The close callback works like a destructor in classes and is executed after the session write callback has been called.
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function close()
	{
		return TRUE;
	}

	/**
	 * The read callback must always return a session encoded (serialized) string, or an empty string if there is no data to read.
	 * @param string $id session ID
	 * @return string
	 */
	#[\ReturnTypeWillChange]
	public function read($id)
	{
		if ($this->_lock($id))
		{
			$queryBuilder = Core_QueryBuilder::select('time', 'value', 'maxlifetime')
				->from('sessions')
				->where('id', '=', $id)
				->limit(1);

			$oDataBase = $queryBuilder->execute();
			$row = $oDataBase->asAssoc()->current();

			$oDataBase->free();

			$this->_read = TRUE;
			//self::$_started = TRUE;
			Core_Session::setStarted();

			if ($row)
			{
				// Session's still available
				if ($row['time'] + $row['maxlifetime'] > time())
				{
					// Update last change time
					$oDataBase = Core_QueryBuilder::update('sessions')
						//->columns(array('time' => 'UNIX_TIMESTAMP(NOW())'))
						->columns(array('time' => time()))
						->where('id', '=', $id)
						->execute();

					$oDataBase->free();

					return base64_decode($row['value']);
				}
				else
				{
					$oDataBase = Core_QueryBuilder::delete('sessions')
						->where('id', '=', $id)
						->execute();

					$oDataBase->free();
				}
			}
		}

		return '';
	}

	/**
	 * The write callback is called when the session needs to be saved and closed.
	 * @param string $id session ID
	 * @param string $value data
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function write($id, $value)
	{
		if ($this->_read/* && $this->_lock($id)*/)
		{
			$value = base64_encode($value);

			$oDataBase = Core_QueryBuilder::update('sessions')
				//->columns(array('time' => 'UNIX_TIMESTAMP(NOW())'))
				->set('value', $value)
				->set('time', time())
				->where('id', '=', $id)
				->execute();

			// Returns the number of rows affected by the last SQL statement
			// If nothing's really was changed affected rowCount will return 0.
			if ($oDataBase->getAffectedRows() == 0 && $value != '')
			{
				$maxlifetime = Core_Session::getMaxLifeTime();

				$oDataBase->free();

				$oDataBase = Core_QueryBuilder::insert('sessions')
					->ignore()
					->columns('id', 'value', 'time', 'maxlifetime')
					->values($id, $value, time(), $maxlifetime)
					->execute();
			}

			$oDataBase->free();

			$this->_unlock($id);

			$this->_read = FALSE;
		}

		return TRUE;
	}

	/**
	 * This callback is executed when a session is destroyed with session_destroy()
	 * @param string $id session ID
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function destroy($id)
	{
		if ($this->_lock($id))
		{
			$oDataBase = Core_QueryBuilder::delete('sessions')
				->where('id', '=', $id)
				->execute();

			$oDataBase->free();

			// для предотвращения автоматической повторной регистрации сеанса
			// при регенерации идентификаора очищать не следует
			//$_SESSION = array();

			$this->_unlock($id);

			return TRUE;
		}

		return FALSE;
	}

	/**
	 * This callback is executed when a session sets maxlifetime
	 * @param int $maxlifetime
	 * @param bool $overwrite overwrite previous maxlifetime
	 * @return boolean
	 */
	public function sessionMaxlifetime($maxlifetime, $overwrite = FALSE)
	{
		$oCore_QueryBuilder = Core_QueryBuilder::update('sessions')
			->set('maxlifetime', $maxlifetime)
			->where('id', '=', session_id());

		!$overwrite
			&& $oCore_QueryBuilder->where('maxlifetime', '<', $maxlifetime);

		$oDataBase = $oCore_QueryBuilder->execute();

		$oDataBase->free();

		return TRUE;
	}

	/**
	 * The garbage collector callback is invoked internally by PHP periodically in order to purge old session data.
	 * @param string $maxlifetime max life time
	 * @return boolean
	 */
	#[\ReturnTypeWillChange]
	public function gc($maxlifetime)
	{
		$oDataBase = Core_QueryBuilder::delete('sessions')
			->where('time + maxlifetime', '<', time())
			->execute();

		$oDataBase->free();

		return TRUE;
	}
	
	/**
	 * This callback is executed when a new session ID is required.
	 * @return string
	 */
	public function create_sid()
	{
		return session_create_id();
	}
	
	/**
	 * This callback is executed when a session is to be started, a session ID is supplied and session.use_strict_mode is enabled
	 * @param string $id Session ID
	 * @return bool
	 */
	public function validateId($id)
	{
		$queryBuilder = Core_QueryBuilder::select('id')
			->from('sessions')
			->where('id', '=', $id)
			->limit(1);

		$oDataBase = $queryBuilder->execute();
		$row = $oDataBase->asAssoc()->current();

		$oDataBase->free();

		return $row !== FALSE;
	}

	/**
	 * Get LOCK name
	 * @param int $id session ID
	 * @return string
	 */
	protected function _getLockName($id)
	{
		return function_exists('hash')
			? hash('sha256', $this->_lockPrefix . '_' . $id)
			: $this->_lockPrefix . '_' . $id;
	}

	/**
	 * Lock session
	 * @param int $id session ID
	 * @return boolean
	 */
	protected function _lock($id)
	{
		// Проверяем, не заблокирована ли уже эта сессия
        if ($this->_currentLockId === $id && $this->_isLockStillValid())
		{
            return TRUE;
        }
        
        // Освобождаем предыдущую блокировку, если есть другая
        if ($this->_currentLockId !== NULL && $this->_currentLockId !== $id)
		{
            $this->_unlock($this->_currentLockId);
        }
		
		$iStartTime = time();

		while (!connection_aborted())
		{
			$oDataBase = $this->_dataBase->setQueryType(0)
				->query('SELECT GET_LOCK(' . $this->_dataBase->quote($this->_getLockName($id)) . ', ' . intval($this->_getLockTimeout) . ') AS `lock`');

			$row = $oDataBase->asAssoc()->current();

			$oDataBase->free();

			if (!is_array($row))
			{
				Core_Session::error('HostCMS session lock error: Get row failure.');
			}

			if (isset($row['lock']) && $row['lock'] == 1)
			{
				// Сохраняем информацию о текущей блокировке
                $this->_currentLockId = $id;
                $this->_currentLockTime = time();
				
				return TRUE;
			}

			if ((time() - $iStartTime) > $this->_lockTimeout)
			{
				Core_Session::error('HostCMS session lock error: Timeout.');
				return FALSE;
			}

			usleep($this->_nextStepDelay);
		}
		return FALSE;
	}

	/**
     * Check if current lock is still valid
     * @return boolean
     */
    protected function _isLockStillValid()
    {
        if (is_null($this->_currentLockTime))
		{
            return FALSE;
        }
        
        // Блокировка действительна не более 30 секунд
        $lockMaxAge = 30;
        return (time() - $this->_currentLockTime) <= $lockMaxAge;
    }
	
	/**
	 * Unlock session
	 * @param int $id session ID
	 * @return boolean
	 */
	protected function _unlock($id)
	{
		// Проверяем, что разблокируем именно текущую сессию
        if ($this->_currentLockId !== $id)
		{
            // Пытаемся разблокировать, если блокировка устарела
            if (!is_null($this->_currentLockId) && !$this->_isLockStillValid())
			{
                $this->_forceUnlock($this->_currentLockId);
            }
        }
		
		$row = $this->_forceUnlock($id);

		// Сбрасываем информацию о блокировке
        $this->_currentLockId = $this->_currentLockTime = NULL;

		if (!is_array($row))
		{
			Core_Session::error('HostCMS session unlock error: Get row failure');
		}

		//return TRUE;
		return is_array($row) && isset($row['released']) && $row['released'] == 1;
	}

	/**
	 * Unlock session
	 * @param int $id session ID
	 */
	protected function _forceUnlock($id)
	{
		$oDataBase = $this->_dataBase->setQueryType(0)
			->query('SELECT RELEASE_LOCK(' . $this->_dataBase->quote($this->_getLockName($id)) . ') AS `lock`');
			
		$row = $oDataBase->asAssoc()->current();
		$oDataBase->free();
		
		return $row;
	}

	/**
	 * Delete all sessions from database
	 */
	static public function flushAll()
	{
		$oDataBase = Core_QueryBuilder::truncate('sessions')->execute();
		$oDataBase->free();
	}
	
	/**
	 * Destructor
	 */
	public function __destruct()
    {
        if (!is_null($this->_currentLockId))
		{
            $this->_unlock($this->_currentLockId);
        }
    }
}