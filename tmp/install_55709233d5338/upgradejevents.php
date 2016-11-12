<?php
/**
 * @copyright	Copyright (C) 2011-2014 GWE Systems Ltd. All rights reserved.
 */

error_reporting(E_ALL);

//Force errors even reporting even more soo if you get a white page, uncomment the below line:
//ini_set("display_errors", 1);

/**
 * CONFIG
 */

$new_site_path = "jupgrade";

/**
 * This path (jupgrade folder) equates to a sub folder of the main Joomla! 1.5 site
 * If you have placed the Joomla! 2.5 or 3.x install outside the 1.5 site, then use ../ to go back a directory, so: "../jupgrade"
 * However if your new Joomla! (2.5 or 3.x) site is located in a different external folder, you can still perform the migration but you have to create this sub folder as a Unix soft link to the
 * Joomla! 2.5 or 3.x site folder as long as the web server user has read access to this other installation. This way you will end with one folder for your new Joomla! install, another folder for your
 * old Joomla! 1.5 site and finally a sub folder in your Joomla! 1.5 folder that will be a soft link to the new Joomla! site folder. The NEWSITEPATH should relate to this soft link.
 *
 * Also to do this you MUST TEMPORARILY comment out these lines of code from libraries/joomla/filesystem/path.php on your Joomla! 2.5 or 3.x installation (reinstate them after the migration)

$path = realpath($path); // needed for substr() later
$fullname = realpath($fullname);

 *
 *************************************************************************************************************************
 *** VERY IMPORTANT Note that if you are not using JUpgrade you must manually map the userids of your event creators ****
 *************************************************************************************************************************

 */

// END OF CONFIG

define("NEWSITEPATH", $new_site_path);

// Set flag that this is a parent file
define('_JEXEC', 1);
define('DS', DIRECTORY_SEPARATOR);
$x = dirname(__FILE__) . DS . NEWSITEPATH;
define('JPATH_BASE', $x);


// create the mainframe object
$_REQUEST['tmpl'] = 'component';

require_once JPATH_BASE . DS . 'includes' . DS . 'defines.php';

require_once JPATH_BASE . DS . 'includes' . DS . 'framework.php';

global $mainframe;
$mainframe =  JFactory::getApplication('administrator');
$mainframe->initialise();

$user = JFactory::getUser();
if (!$user->get("isRoot"))
{
    echo "Must be logged in to Joomla 1.6 site backend as super admin";
    exit(0);
}


$upgrade = new jUpgradeJEvents();
$upgrade->upgrade();

/**
 * Upgrade class for JEvents migration
 */
class jUpgradeJEvents
{

    private $db_old = false;
    private $db_new = false;
    private $oldDatabaseName = "";
    private $newDatabaseName = "";

    public function __construct()
    {
        // Must be logged in as super admin to do this!
        $this->db_new = JFactory::getDBO();

        $oldconfig = dirname(__FILE__) . "/configuration.php";
        jimport("joomla.filesystem.file");
        $config = JFile::read($oldconfig);
        $lines = explode("\n", $config);
        $this->config_old = array();
        $this->config_old['driver'] = 'mysqli';

        foreach ($lines as $line)
        {
            $this->getConfigField('host', $line);
            $this->getConfigField('user', $line);
            $this->getConfigField('password', $line);
            $this->getConfigField('db', $line);
            $this->getConfigField('dbprefix', $line);
        }
        // options uses prefix and NOT dbprefix
        $this->config_old['prefix'] = $this->config_old['dbprefix'];
        $this->config_old['database'] = $this->config_old['db'];
        $this->db_old = JDatabase::getInstance($this->config_old);

        $this->config_new = new JConfig();

        $this->oldDatabaseName =  $this->config_old['db'];
        $this->newDatabaseName =  $this->config_new->db;

    }

    private function getConfigField($field, $line)
    {
        //if (strpos($line, "public \$$field = ") !== false)
        if (strpos($line, "var \$$field = ") !== false)
        {
            //$this->config_old[$field] = trim(str_replace("public \$$field = '", '', $line));
            $this->config_old[$field] = trim(str_replace("var \$$field = '", '', $line));
            $this->config_old[$field] = substr($this->config_old[$field], 0, strlen($this->config_old[$field]) - 2);
        }


    }

    public function migrateTables()
    {
        echo '<script language="javascript">';
        echo 'alert("message successfully sent")';
        echo '</script>';
        // Migrate core tables to be adjusted later
        $this->migrateBlock("jev_");
        $this->migrateBlock("jevlocation_");
        $this->migrateBlock("aje_");

        $this->cleanJeventsCategories();

        // convert JEvents categories first
        $this->jeventsCategories(0, 'com_jevents');

        $this->db_new->setQuery("SELECT * FROM #__categories where extension='com_jevents' ");
        $categories = $this->db_new->loadObjectList('id');

        // Getting the categories id's
        $categoryMaps = $this->getMapList('categories', 'com_jevents');

        // now do colours and admins!
        $this->db_old->setQuery("SELECT * FROM #__jevents_categories");
        $jevcats = $this->db_old->loadObjectList();
        foreach ($jevcats as $jevcat)
        {
            if (!array_key_exists($jevcat->id, $categoryMaps))
                continue;
            $cat = $categories[$categoryMaps[$jevcat->id]->new];
            $params = json_decode($cat->params);
            $params->catcolour = $jevcat->color;
            $params->admin = $jevcat->admin;
            $params->overlaps = 0;
            $params = json_encode($params);

            $this->db_new->setQuery("UPDATE #__categories set params=" . $this->db_new->quote($params) . " where id=" . $categoryMaps[$jevcat->id]->new);
            $this->db_new->query();
        }

        // Rebuild the categories table
        $table = JTable::getInstance('Category', 'JTable', array('dbo' => $this->db_new));
        if (!$table->rebuild())
        {
            echo JError::raiseError(500, $table->getError());
        }
        echo "migrated categories<br/>";

        // copy JEvents tables
        $this->migrateBlock("jevents_");

        echo "migrated main tables<br/>";

        // Fix calendar  and event references to catid
        foreach ($categoryMaps as $cat)
        {
            // Add ridiculous offset to stop double mapping!
            $this->db_new->setQuery("UPDATE #__jevents_icsfile set catid =" . ($cat->new + 999999) . " where catid=" . $cat->old);
            $this->db_new->query();

            $this->db_new->setQuery("UPDATE #__jevents_vevent set catid =" . ($cat->new + 999999) . "  where catid=" . $cat->old);
            $this->db_new->query();
        }
        // reomve the offset now!
        $this->db_new->setQuery("UPDATE #__jevents_icsfile set catid=catid-999999 where catid>999999");
        $this->db_new->query();
        $this->db_new->setQuery("UPDATE #__jevents_vevent set catid=catid-999999 where catid>999999");
        $this->db_new->query();

        echo "updated category references<br/>";

        // Update Access levels
        // NOTES check new access rules!
        $this->db_new->setQuery("UPDATE #__jevents_icsfile set access=access+1");
        $this->db_new->query();
        $error = $this->db_new->getErrorMsg();
        if ($error)
        {
            throw new Exception($error);
        }

        $this->db_new->setQuery("UPDATE #__jevents_vevent set access=access+1");
        $this->db_new->query();
        $error = $this->db_new->getErrorMsg();
        if ($error)
        {
            throw new Exception($error);
        }

        echo "reset access levels<br/>";

        // Fix individual permissions
        $this->db_new->setQuery("SELECT id, categories FROM #__jev_users WHERE categories<>'all' AND categories<>'' ");
        $users = $this->db_new->loadObjectList();
        foreach ($users as $user)
        {
            $usercats = explode("|", $user->categories);
            $cats = array();
            foreach ($usercats as $uc)
            {
                if (array_key_exists(intval($uc), $categoryMaps))
                {
                    $cats[] = $categoryMaps[intval($uc)]->new;
                }
            }
            $usercats = implode("|", $cats);
            $this->db_new->setQuery("UPDATE #__jev_users set categories=" . $this->db_new->quote($usercats) . " WHERE id=$user->id");
            $this->db_new->query();
        }

        echo "migrated user permissions<br/>";


        // Managed Locations
        if (JFile::exists(JPATH_SITE . "/components/com_jevlocations/jevlocations.php"))
        {
            $this->jeventsLocationCategories();

            // Getting the categories id's
            $categoryMaps = $this->getMapList('categories', 'com_jevlocations%');

            // Fix location references to catid
            foreach ($categoryMaps as $cat)
            {
                // Add ridiculous offset to stop double mapping!
                $this->db_new->setQuery("UPDATE #__jev_locations set catid =" . ($cat->new + 999999) . " where catid=" . $cat->old);
                $this->db_new->query();
                // Add ridiculous offset to stop double mapping!
                $this->db_new->setQuery("UPDATE #__jev_locations set loccat =" . ($cat->new + 999999) . " where loccat=" . $cat->old);
                $this->db_new->query();
            }
            // reomve the offset now!
            $this->db_new->setQuery("UPDATE #__jev_locations set catid=catid-999999 where catid>999999");
            $this->db_new->query();
            $this->db_new->setQuery("UPDATE #__jev_locations set loccat=loccat-999999 where loccat>999999");
            $this->db_new->query();
            echo "migrated managed locations<br/>";
        }


        // Managed People
        if (JFile::exists(JPATH_SITE . "/components/com_jevpeople/jevpeople.php"))
        {
            $this->jeventsPeopleCategories(0, 'com_jevpeople%');
            // Getting the categories id's
            // Note we have changed the section name!
            $categoryMaps = $this->getMapList('categories', 'com_jevpeople');

            // Fix location references to catid
            foreach ($categoryMaps as $cat)
            {
                // Add ridiculous offset to stop double mapping!
                $this->db_new->setQuery("UPDATE #__jev_people set catid0 =" . ($cat->new + 999999) . " where catid0=" . $cat->old);
                $this->db_new->query();
                $this->db_new->setQuery("UPDATE #__jev_people set catid1 =" . ($cat->new + 999999) . " where catid1=" . $cat->old);
                $this->db_new->query();
                $this->db_new->setQuery("UPDATE #__jev_people set catid2 =" . ($cat->new + 999999) . " where catid2=" . $cat->old);
                $this->db_new->query();
                $this->db_new->setQuery("UPDATE #__jev_people set catid3 =" . ($cat->new + 999999) . " where catid3=" . $cat->old);
                $this->db_new->query();
                $this->db_new->setQuery("UPDATE #__jev_people set catid4 =" . ($cat->new + 999999) . " where catid4=" . $cat->old);
                $this->db_new->query();
            }
            // reomve the offset now!
            $this->db_new->setQuery("UPDATE #__jev_people set catid0=catid0-999999 where catid0>999999");
            $this->db_new->query();
            $this->db_new->setQuery("UPDATE #__jev_people set catid1=catid1-999999 where catid1>999999");
            $this->db_new->query();
            $this->db_new->setQuery("UPDATE #__jev_people set catid2=catid2-999999 where catid2>999999");
            $this->db_new->query();
            $this->db_new->setQuery("UPDATE #__jev_people set catid3=catid3-999999 where catid3>999999");
            $this->db_new->query();
            $this->db_new->setQuery("UPDATE #__jev_people set catid4=catid4-999999 where catid4>999999");
            $this->db_new->query();
            echo "migrated managed people<br/>";
        }

        // Rebuild the categories table
        $table = JTable::getInstance('Category', 'JTable', array('dbo' => $this->db_new));
        if (!$table->rebuild())
        {
            echo JError::raiseError(500, $table->getError());
        }
        echo "migrated categories<br/>";

        return true;

    }

    private function migrateBlock($prefix)
    {

        $this->db_old->setQuery("SHOW TABLES LIKE '" . $this->db_old->getPrefix() . "$prefix%'");
        $tables = $this->db_old->loadColumn();
        // strange bug on one site needed this
        //$tables = $this->db_old->loadObjectList();

        if ($tables && count($tables) > 0)
        {

            foreach ($tables as $table)
            {
                // strange bug on one site needed this
                //$table = array_values(get_object_vars($table));
                //$table = $table[0];


                //$table = current(get_object_vars($table));
                $newtable = str_replace($this->db_old->getPrefix(), $this->db_new->getPrefix(), $table);
                //Wow wow, no need to drop tables, we already ask users to do this first. This just causes this isis error.
                //$this->db_new->setQuery("DROP TABLE " . $newtable);
                //$this->db_new->query();
                $this->copyTable($table, $newtable);

            }
        }

    }

    private function cleanJeventsCategories()
    {

        // clean out new table of entries relating to jevents
        $sql = "DELETE FROM #__categories";
        $sql .= " WHERE extension like 'com_jev%'";
        $this->db_new->setQuery($sql);
        $this->db_new->query();

        // Create jupgrade table just incase its not a jupgrade upgrade!
        $sql = "CREATE TABLE IF NOT EXISTS `#__jupgrade_categories` (
  `old` int(11) NOT NULL,
  `new` int(11) NOT NULL,
  `section` varchar(255) NOT NULL DEFAULT '0'
)  DEFAULT CHARSET=utf8;";
        $this->db_new->setQuery($sql);
        $this->db_new->query();

        $sql = "DELETE FROM #__jupgrade_categories";
        $sql .= " WHERE section like 'com_jev%'";
        $this->db_new->setQuery($sql);
        $this->db_new->query();


    }

    private function jeventsCategories($parent = 0, $section='com_jevents')
    {

        $sql = "SELECT  id as sid, parent_id,  section AS extension, `title`, `alias`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`FROM #__categories";
        if (strpos($section, '%') > 0)
        {
            $sql .= " WHERE section like '$section' and parent_id=  $parent";
        }
        else
        {
            $sql .= " WHERE section='$section' and parent_id=  $parent";
        }
        $this->db_old->setQuery($sql);
        $rows = $this->db_old->loadAssocList('sid');

        if (count($rows) == 0)
            return;

        // Do some custom post processing on the list.
        foreach (array_keys($rows) as $i)
        {
            $row = & $rows[$i];
            $row['description'] = str_replace("'", "&#39;", $row['description']);
            $row['params'] = $this->convertParams($row['params']);
            $row['access'] = $row['access'] + 1;
            $row['language'] = '*';

            // Correct alias
            if ($row['alias'] == "")
            {
                $row['alias'] = JFilterOutput::stringURLSafe($row['title']);
            }
        }

        // Insert the categories
        //
        foreach ($rows as $catrow)
        {
            // Convert the array into an object.
            $catrow = (object) $catrow;

            $this->insertCategory($catrow, $parent);
            //$this->insertAsset($row);
            // child categories
            $this->jeventsCategories($catrow->sid, $section);
        }

    }

    private function jeventsPeopleCategories($parent = 0, $section='com_jevents'){
        $sql = "SELECT  id as sid, parent_id,  section AS extension, `title`, `alias`, `description`, `published`, `checked_out`, `checked_out_time`, `access`, `params`FROM #__categories";
        if (strpos($section, '%') > 0)
        {
            $sql .= " WHERE section like '$section' and parent_id=  $parent";
        }
        else
        {
            $sql .= " WHERE section='$section' and parent_id=  $parent";
        }
        $this->db_old->setQuery($sql);
        $rows = $this->db_old->loadAssocList('sid');

        if (count($rows) == 0)
            return;

        // Do some custom post processing on the list.
        foreach (array_keys($rows) as $i)
        {
            $row = & $rows[$i];
            $row['description'] = str_replace("'", "&#39;", $row['description']);
            // move the type value to the params
            $row['params'] .= $row['params'] !=""?"\n":"";
            $row['params'] .= 'type="'.str_replace("com_jevpeople_type","",$row['extension']).'"';
            $row['params'] = $this->convertParams($row['params']);
            $row['extension'] = "com_jevpeople";
            $row['access'] = $row['access'] + 1;
            $row['language'] = '*';

            // Correct alias
            if ($row['alias'] == "")
            {
                $row['alias'] = JFilterOutput::stringURLSafe($row['title']);
            }
        }

        // Insert the categories
        //
        foreach ($rows as $catrow)
        {
            // Convert the array into an object.
            $catrow = (object) $catrow;

            $this->insertCategory($catrow, $parent);
            //$this->insertAsset($row);
            // child categories
            $this->jeventsPeopleCategories($catrow->sid, $section);
        }

        $this->checkCategoryAssets("com_jevpeople");
    }

    private function jeventsLocationCategories()
    {
        $database = $this->config_old['database'];
        $prefix = $this->config_old['prefix'];

        // Did we already migrate from old to new locations category table structure
        $query = "SELECT COUNT(*) AS count
      FROM information_schema.tables
      WHERE table_schema = '$database'
      AND table_name = '#__jevlocation_categories' ";

        $this->db_old->setQuery($query);
        $oldexists = $this->db_old->loadResult();

        // Drop the table initially
        $query = "DROP TABLE IF EXISTS #__jevlocation_categories";
        $this->db_new->setQuery($query);
        $this->db_new->query();

        // Check for query error.
        $error = $this->db_new->getErrorMsg();
        if ($error)
        {
            throw new Exception($error);
        }

        if ($oldexists)
        {
            $from = $database . "." . $prefix . "jevlocation_categories";
        }
        else
        {
            $from = $database . "." . $prefix . "categories";
        }
        $query = "CREATE TABLE #__jevlocation_categories LIKE " . $from;
        $this->db_new->setQuery($query);
        $this->db_new->query();

        // Check for query error.
        $error = $this->db_new->getErrorMsg();

        if ($error)
        {
            throw new Exception($error);
        }


        if ($oldexists)
        {
            $query = "INSERT INTO #__jevlocation_categories SELECT * FROM " . $from;
        }
        else
        {
            $query = "INSERT INTO #__jevlocation_categories SELECT * FROM " . $from . " WHERE section like 'com_jevlocation%'";
        }
        $this->db_new->setQuery($query);
        $this->db_new->query();

        // Check for query error.
        $error = $this->db_new->getErrorMsg();

        if ($error)
        {
            throw new Exception($error);
        }

    }

    public function insertCategory($object, $parent=0)
    {
        // Get old id
        $oldlist = new stdClass();
        $oldlist->section = $object->extension;
        $oldlist->old = $object->sid;
        unset($object->sid);

        // If has parent made $path and get parent id
        if ($parent != 0)
        {
            $parent = intval($parent);
            $query = "SELECT cc.*, uc.* FROM #__jupgrade_categories as uc LEFT JOIN #__categories as cc ON cc.id=uc.new WHERE old = '$parent' LIMIT 1";
            $this->db_new->setQuery($query);
            $parentrow = $this->db_new->loadObject();

            $error = $this->db_new->getErrorMsg();
            if ($error)
            {
                throw new Exception($error);
            }
            if (!$parentrow)
                continue;

            $object->path = $parentrow->path . "/" . $object->alias;

            $object->parent_id = $parentrow->new;
        }
        else
        {
            $object->parent_id = 1;
            $object->path = $object->alias;
        }

        // Insert the row
        if (!$this->db_new->insertObject('#__categories', $object))
        {
            throw new Exception($this->db_new->getErrorMsg());
        }

        // Returning sid needed by insertAsset()
        $object->sid = $oldlist->old;

        // Get new id
        $oldlist->new = $this->db_new->insertid();

        // Save old and new id
        if (!$this->db_new->insertObject('#__jupgrade_categories', $oldlist))
        {
            throw new Exception($this->db_new->getErrorMsg());
        }

        return true;

    }

    private  function checkCategoryAssets($extension)
    {
        $db = JFactory::getDbo();
        $db->setQuery("SELECT * FROM #__categories WHERE asset_id=0 and extension='$extension' order by level , id");
        $missingassets = $db->loadObjectList();
        if (count($missingassets) > 0)
        {
            foreach ($missingassets as $missingasset)
            {
                $this->insertAsset($missingasset,$extension);
            }
        }

        // Fix assets with no permissions set!
        $db->setQuery("SELECT * FROM #__assets WHERE name like '$extension.category.%' AND rules=''");
        $blankruleassets = $db->loadObjectList('id');
        if ($blankruleassets && count ($blankruleassets)>0){
            $db->setQuery("UPDATE #__assets SET rules='".'{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}'."' WHERE name like '$extension.category.%' AND rules=''");
            $db->query();
        }

    }

    private function insertAsset($object, $extension)
    {
        $db = JFactory::getDbo();
        // Getting the asset table
        $table = JTable::getInstance('Asset', 'JTable', array('dbo' => $db));

        // Getting the categories id's
        $db->setQuery("SELECT * FROM #__categories WHERE extension='$extension'");
        $categories = $db->loadObjectList('id');

        $db->setQuery("SELECT * FROM #__assets WHERE name like '$extension.category.%'");
        $assets = $db->loadObjectList('id');

        $db->setQuery("SELECT * FROM #__assets WHERE name = '$extension' and parent_id=1");
        $rootasset = $db->loadObject();

        $assets[$rootasset->id] = $rootasset;

        //
        // Correct extension
        //
        $id = $object->id;
        $table->name = "$extension.category.{$id}";

        // Setting rules values
        $table->rules = '{"core.create":[],"core.delete":[],"core.edit":[],"core.edit.state":[],"core.edit.own":[]}';
        $table->title = $db->escape($object->title);

        if ($object->parent_id==1)
        {
            $table->parent_id = $rootasset->id;
        }
        else if (array_key_exists($object->parent_id, $categories) && $categories[$object->parent_id]->asset_id > 0)
        {
            $table->parent_id = $categories[$object->parent_id]->asset_id;
        }

        // Make sure this asset doesn't exist already
        $db->setQuery("SELECT * FROM #__assets WHERE name = ".$db->quote($table->name));
        $asset = $db->loadObject();
        if (!$asset){
            // Insert the asset
            $table->store();
        }
        else {
            $table = $asset;
        }

        // updating the category asset_id;
        $updatetable = '#__categories';
        $query = "UPDATE {$updatetable} SET asset_id = {$table->id}"
            . " WHERE id = {$id}";
        $db->setQuery($query);
        $db->query();

        // Check for query error.
        $error = $db->getErrorMsg();

        if ($error)
        {
            throw new Exception($error);
            return false;
        }

        return true;

    }

    public function upgrade()
    {
        try {
            echo '<script language="javascript">';
            echo 'alert("message successfully sent")';
            echo '</script>';
            $this->migrateTables();
        }
        catch (Exception $e) {
            echo JError::raiseError(500, $e->getMessage());

            return false;
        }

        return true;

    }

    protected function convertParams($params)
    {
        jimport('joomla.html.parameter');
        // JParameter Doesn't work in J3.
        //$temp = new JParameter($params);
        //Replace JRegistry instead:
        $temp = new JRegistry($params);
        $object = $temp->toObject();

        return json_encode($object);

    }

    /**
     * Internal function to get original database prefix
     *
     * @return	an original database prefix
     * @since	0.5.3
     * @throws	Exception
     */
    public function getMapList($table = 'categories', $section = false)
    {
        // Getting the categories id's
        $query = "SELECT *"
            . " FROM ".$this->db_new->getPrefix()."jupgrade_{$table}";

        if ($section !== false)
        {
            if (strpos($section, '%') > 0)
            {
                $query .= " WHERE section like '{$section}'";
            }
            else
            {
                $query .= " WHERE section = '{$section}'";
            }
        }

        $this->db_new->setQuery($query);
        $data = $this->db_new->loadObjectList('old');

        // Check for query error.
        $error = $this->db_new->getErrorMsg();

        if ($error)
        {
            throw new Exception($error);
            return false;
        }

        return $data;

    }

    /**
     * Copy table to old site to new site
     *
     * @return	boolean
     * @since 1.1.0
     * @throws	Exception
     */
    protected function copyTable($from, $to)
    {

        // Check if table exists
        $database = $this->config_old['database'];
        $prefix = $this->config_old['prefix'];
        $from = preg_replace('/#__/', $prefix, $from);

        $query = "SELECT COUNT(*) AS count
      FROM information_schema.tables
      WHERE table_schema = '$database'
      AND table_name = '$from'";

        $this->db_old->setQuery($query);
        $res = $this->db_old->loadResult();

        if ($res == 0)
        {
            $success = false;
        }
        else
        {
            // Drop the table initially
            $query = "DROP TABLE IF EXISTS {$to}";
            $this->db_new->setQuery($query);
            $this->db_new->query();

            // Check for query error.
            $error = $this->db_new->getErrorMsg();

            if ($error)
            {
                throw new Exception($error);
            }

            $oldDb = $this->oldDatabaseName;
            $newDb = $this->newDatabaseName;
            $query = "CREATE TABLE $newDb.$to LIKE $oldDb.$from";

            $this->db_new->setQuery($query);
            $this->db_new->query();

            // Check for query error.
            $error = $this->db_new->getErrorMsg();

            if ($error)
            {
                throw new Exception($error);
            }

            $query = "INSERT INTO $newDb.$to SELECT * FROM $oldDb.$from";
            $this->db_new->setQuery($query);
            $this->db_new->query();

            // Check for query error.
            $error = $this->db_new->getErrorMsg();

            if ($error)
            {
                throw new Exception($error);
            }

            $success = true;
        }


        return $success;


    }

}