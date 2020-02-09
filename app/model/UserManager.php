<?php

namespace App\Model;

use Nette;
use Nette\Security\Passwords;


/**
 * Users management.
 */
class UserManager implements Nette\Security\IAuthenticator
{
	const
		TABLE_NAME = 'admin',
		COLUMN_ID = 'id',
		COLUMN_NAME = 'username',
		COLUMN_PASSWORD_HASH = 'password',
		COLUMN_ROLE = 'role',
		COLUMN_EMAIL = 'email',
        COLUMN_FIRST_NAME = 'first_name',
        COLUMN_LAST_NAME = 'last_name',
		COLUMN_PHONE = 'phone';


	/** @var Nette\Database\Context */
	protected $db;

	/** @var LoginFailModel */
	protected $loginFailModel;

	public function __construct(Nette\Database\Context $database, LoginFailModel $loginFailModel)
	{
		$this->db = $database;
		$this->loginFailModel = $loginFailModel;
	}


	/**
	 * Performs an authentication.
	 * @return Nette\Security\Identity
	 * @throws Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->db->table(self::TABLE_NAME)->where('(' . self::COLUMN_NAME . ' = ? OR ' . self::COLUMN_EMAIL . ' = ?)', $username, $username)->fetch();

		if (!$row)
		{
			$this->loginFailModel->increaseFailCountByIP();
			throw new Nette\Security\AuthenticationException('Špatné přihlašovací jméno', self::IDENTITY_NOT_FOUND);
		}
		elseif (!Passwords::verify($password, $row[self::COLUMN_PASSWORD_HASH]))
		{
			$this->loginFailModel->increaseFailCountByIP();
			throw new Nette\Security\AuthenticationException('Špatné heslo', self::INVALID_CREDENTIAL);
		}
		elseif (Passwords::needsRehash($row[self::COLUMN_PASSWORD_HASH]))
		{
			$row->update(array(
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr[self::COLUMN_PASSWORD_HASH]);

		return new Nette\Security\Identity($row[self::COLUMN_ID], $row[self::COLUMN_ROLE], $arr);
	}


    /**
     * @param $username
     * @param $password
     * @param $email
     * @param $firstName
     * @param $lastName
     * @throws DuplicateNameException
     */
	public function add($username, $password, $email, $firstName, $lastName, $role, $phone)
	{
		try
        {
			$this->db->table(self::TABLE_NAME)->insert(array(
				self::COLUMN_NAME => $username,
				self::COLUMN_PASSWORD_HASH => Passwords::hash($password),
				self::COLUMN_EMAIL => $email,
				self::COLUMN_FIRST_NAME => $firstName,
				self::COLUMN_LAST_NAME => $lastName,
				self::COLUMN_ROLE => $role,
				self::COLUMN_PHONE => $phone
			));
		}
        catch (Nette\Database\UniqueConstraintViolationException $e)
        {
			throw new DuplicateNameException;
		}
	}

	public function edit($id, $username, $email, $firstName, $lastName, $role, $phone)
	{
		$this->db->query('
			UPDATE admin
			SET username = ?, email = ?, first_name = ?, last_name = ?, role = ?, phone = ?
			WHERE id = ?
		', $username, $email, $firstName, $lastName, $role, $phone, $id);
	}

	public function changePassword($id, $password)
	{
		$this->db->query('UPDATE admin SET password = ? WHERE id = ?', Passwords::hash($password), $id);
	}

	public function delete($id)
	{
		$this->db->query('DELETE FROM admin WHERE id = ?', $id);
	}

	public function getUsers()
	{
		return $this->db->query('
			SELECT
				id,
				username,
				CONCAT(first_name, " ", last_name) AS fullName,
				first_name AS firstName,
				last_name AS lastName,
				email,
				role,
				phone,
				password AS passwordHash
			FROM admin
			ORDER BY username
		')->fetchAll();
	}

	public function getByUsername($username)
	{
		return $this->db->query('SELECT id FROM admin WHERE LOWER(username) LIKE LOWER(?)', $username)->fetchAll();
	}

	public function getByEmail($email)
	{
		return $this->db->query('SELECT id FROM admin WHERE email LIKE ?', $email)->fetchAll();
	}

	/**
	 * @param $id
	 * @return bool|Nette\Database\IRow|Nette\Database\Row
	 */
	public function getByID($id)
	{
		return $this->db->query('
			SELECT
				id, username, email, first_name AS firstName, last_name AS lastName, role, phone,
				CONCAT(first_name, " ", last_name) AS fullName
			FROM admin
			WHERE id = ?
		', $id)->fetch();
	}

	public function getUserCount()
	{
		$count = $this->db->query('SELECT COUNT(id) AS `count` FROM admin')->fetch();

		return $count->count;
	}

	public function verifyPassword($id, $password)
	{
		$user = $this->db->query('SELECT id, password FROM admin WHERE id = ?', $id)->fetch();

		return !$user ? false : Passwords::verify($password, $user->password);
	}
}



class DuplicateNameException extends \Exception
{}
