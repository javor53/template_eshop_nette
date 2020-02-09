<?php

namespace App\Model;

use Nette;
use Nette\Utils\Strings;
/*
    database req
 * name:collections
 * id
 * title
 * description
 * url
 *  */
class CollectionsModel extends ManagerModel
{
    /**
     * @param $id
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     * @throws \Exception
     */
    public function getByID($id)
    {
        $collection = $this->database->query('
            SELECT
                id, title, description, url
            FROM collections
            WHERE id = ?
        ', $id)->fetch();

        if ($collection === false)
            throw new \Exception('collections not found');

        return $collection;
    }

    /**
     * @param string $url
     * @return bool|Nette\Database\IRow|Nette\Database\Row
     * @throws \Exception
     */
    public function getByURL($url)
    {
        $collection = $this->database->query('
            SELECT
                id, title, description, url
            FROM collections
            WHERE url LIKE ?
        ', $url)->fetch();

        if ($collection === false)
            throw new \Exception('collections not found');

        return $collection;
    }

    /**
     * @param string $orderBy
     * @param bool $desc
     * @param bool $productCount
     * @return array|Nette\Database\IRow[]
     */
    public function getCollections(){
        
         return $this->database->table('collections');
        
    }
    
    public function getAll($orderBy = 'title', $desc = false, $productCount = false)
    {
        $collections = $this->database->query('
            SELECT id, title, description, url
            FROM collections
            ORDER BY ' . $orderBy . ' ' . ($desc ? 'DESC' : '') . '
        ')->fetchAll();

        if ($productCount)
        {
            foreach ($collections as $collection)
            {
                $count = $this->database->query('
                    SELECT COUNT(id) AS productCount FROM product
                    WHERE collection_id = ?
                ', $collection->id)->fetch();

                $collection->productCount = $count->productCount;
            }
        }

        return $collections;
    }


    public function insert($name, $description)
    {
        if (!empty($this->database->query('SELECT id FROM collections WHERE title LIKE ?', $name)->fetchAll()))
            throw new \Exception('Název používá jiná kolekce');

        $url = Strings::webalize($name);
        $addIdToUrl = false;

        try
        {
            $urlCollection = $this->getByURL($url);
            $addIdToUrl = true;
        }
        catch (\Exception $e)
        {
            $addIdToUrl = false;
        }

        $collection = $this->database->table('collections')->insert([
            'title' => $name,
            'description' => $description,
            'url' => $url
        ]);

        if (!$collection)
            throw new \Exception('Chyba při ukládání');

        if ($addIdToUrl)
        {
            $url = $collection->id . '-' . $url;
            $this->database->query('UPDATE collections SET url = ? WHERE id = ?', $url, $collection->id);
        }

        return $collection;
    }

    /**
     * @param $id
     * @param $name
     * @param $description
     * @throws \Exception
     */
    public function edit($id, $name, $description)
    {
        if (!$this->db->query('SELECT id FROM collections WHERE id = ?', $id)->fetch())
            throw new \Exception('Kolekce neexistuje');

        $collections = $this->db->query('SELECT id FROM collections WHERE name LIKE ?', $name)->fetchAll();

        if (!empty($collection) && $collections[0]->id != $id)
            throw new \Exception('Název používá jiná kolekce');

        $url = Strings::webalize($name);

        try
        {
            $collection = $this->getByURL($url);

            if ($collection->id != $id)
                $url = $collection->id . '-' . $url;
        }
        catch (\Exception $e)
        {
        }

        $this->db->query('
            UPDATE collections SET `title` = ?, description = ?, url = ?
            WHERE id = ?
        ', $name, $description, $url, $id);
    }

    public function deleteByID($id)
    {
        try
        {
            $collection = $this->getByID($id);

            $this->database->query('DELETE FROM images WHERE collection_id = ?', $collection->id);
            $this->database->query('DELETE FROM collections WHERE id = ?', $collection->id);
        }
        catch (\Exception $e)
        {
        }
    }

    public function getCollectionsCount()
    {
        $count = $this->database->query('SELECT COUNT(id) AS `count` FROM collections')->fetch();

        return $count->count;
    }
    
    public function isCollectionEmpty($idCol){
        $id = $this->database->query('SELECT title FROM collections WHERE id = ?',$idCol)->fetch();
        //$image = $this->database->query('SELECT id FROM images WHERE collection_id LIKE ?',$id)->fetch();
        $idt = $id->title;
        if (!empty($this->database->query('SELECT * FROM images WHERE collection_id = ?', $idCol )
                ->fetchAll())){
            return false;
            
        }
        else{
            return true;
        }
    }
}