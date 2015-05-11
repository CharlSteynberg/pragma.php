<?

// EXTCLSTXT :: global constant
// --------------------------------------------------------------------------------------
   define('EXTCLSTXT', explode('/***/', file_get_contents(__FILE__))[2]);
// --------------------------------------------------------------------------------------



// create extensible class && load cfg + ini
// --------------------------------------------------------------------------------------
   function create_class($cls, $ini=false)
   {
   // validate
   // -----------------------------------------------------------------------------------
      if (class_exists($cls, false))
      { return true; }
   // -----------------------------------------------------------------------------------

   // eval DYNCLSTXT with class name && run _init function
   // -----------------------------------------------------------------------------------
      if (is_dir("sys/$cls") && is_dir("pub/app/$cls"))
      { throw new Exception('duplicate class name "'.$cls.'" in paths: "sys/" and "pub/app/"'); }

      $dir = (is_dir("sys/$cls") ? 'sys' : (is_dir("pub/app/$cls") ? 'pub/app' : null));

      if ($ini === true)
      {
         if ($dir === null)
         { throw new Exception('"'.$cls.'" does not exist in either "sys/" or "pub/app/"'); }

         $pth = "$dir/$cls";

         if (!is_readable($pth))
         {
            $m = (!is_dir($pth) ? 'undefined' : 'forbidden');
            throw new Exception('"'.$pth.'" is '.$m);
         }
      }

      eval('class '.$cls." {".EXTCLSTXT."\n}");
      $cls::_init($dir); // !! leave this here !!
   // -----------------------------------------------------------------------------------

   // set class configuration if defined
   // -----------------------------------------------------------------------------------
      $jsm = 'cfg/'.$cls.'/_init.jsam';
      $jsn = 'cfg/'.$cls.'/_init.json';
      $cfg = (file_exists($jsm) ? $jsm : $jsn);

      if (file_exists($cfg)){ $cls::set('conf', parse::file($cfg)); }
   // -----------------------------------------------------------------------------------

   // to load _init or not
   // -----------------------------------------------------------------------------------
      $sci = "sys/$cls/_init.php";
      $pci = "pub/app/$cls/_init.php";
      $cip = (file_exists($sci) ? $sci : $pci);

      if (($ini === true) && file_exists($cip)) { require($cip); }
   // -----------------------------------------------------------------------------------

   // if nothing went wrong, return true
   // -----------------------------------------------------------------------------------
      return true;
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// create classes dynamically
// --------------------------------------------------------------------------------------
   spl_autoload_register(function($cls)
   {
      create_class($cls, true);
   });
// --------------------------------------------------------------------------------------



// extract between block comment lines to be eval'led by: create_class()
// --------------------------------------------------------------------------------------
   class extClass
   {
/***/
   // locals
   // -----------------------------------------------------------------------------------
      private static $crp = 0;                      // class root path
      private static $atr = 0;                      // class attribtes library
      private static $loc = 0;                      // lock attributes list
   // -----------------------------------------------------------------------------------


   // initialize class
   // -----------------------------------------------------------------------------------
      public static function _init($p)
      {
         self::$crp = $p;
         self::$atr = new obj();
         self::$loc = new obj();
      }
   // -----------------------------------------------------------------------------------


   // set map value
   // -----------------------------------------------------------------------------------
      public static function set($at1, $at2=null, $at3=null)
      {
         if (typeOf($at1) === str)
         { $at1 = [$at1=>$at2]; $at2 = $at3; }

         foreach($at1 as $key => $val)
         {
            if (isset(self::$loc->$key))
            { throw new Exception(__CLASS__.'.'.$key.' is locked'); }

            map::set(self::$atr, $key, $val);

            if (($at2 == 'lock') || ($at2 == true))
            { self::$loc->$key = true; }
         }

         return true;
      }
   // -----------------------------------------------------------------------------------


   // has
   // -----------------------------------------------------------------------------------
      public static function has($k)
      {
         return (property_exists(self::$atr, $k) ? true : false);
      }
   // -----------------------------------------------------------------------------------


   // get map
   // -----------------------------------------------------------------------------------
      public static function get($k, $f=null, $b=null)
      {
         return map::get(self::$atr, $k, $f, $b);
      }
   // -----------------------------------------------------------------------------------


   // add(to) map (array), create if not exist
   // -----------------------------------------------------------------------------------
      public static function add($key, $val)
      {
         $emv = map::get(self::$atr, $key);

         if ($emv === null) { $emv=[]; }

         $emv[] = $val;

         map::set(self::$atr, $key, $emv);

         return true;
      }
   // -----------------------------------------------------------------------------------


   // delete (from) map
   // -----------------------------------------------------------------------------------
      public static function del($p)
      {
         sys::stack(__CLASS__, 'del', $p);

         $p = explode('.', $p);
         $k = val(array_pop($p));
         $p = implode($p, '.');

         $o = map::get(self::$atr, $p);
         $t = typeOf($o);

         if (($t !== obj) && ($t !== arr))
         { throw new Exception('"'.$k.'" is not an object or array'); }

         if ($t === obj)
         { unset($o->$k); }
         else
         {
            unset($o[$k]);
            $o = array_values($o);
         }

         map::set(self::$atr, $p, $o);
         return true;
      }
   // -----------------------------------------------------------------------------------


   // remove (from) map :: del alias
   // -----------------------------------------------------------------------------------
      public static function rem($p)
      { return self::del($p); }
   // -----------------------------------------------------------------------------------


   // class called statically
   // -----------------------------------------------------------------------------------
      public static function __callStatic($key, $arg)
      {
      // class name
      // --------------------------------------------------------------------------------
         $cls = __CLASS__;
      // --------------------------------------------------------------------------------

      // called map value and value type
      // --------------------------------------------------------------------------------
         $cav = map::get(self::$atr, $key);
         $tpe = typeOf($cav);

         if (isset($arg[1]) && ($arg[1] === '_{arg:0}_'))
         { $arg = $arg[0]; }
      // --------------------------------------------------------------------------------

      // function :: defined module
      // --------------------------------------------------------------------------------
         if ($tpe === fnc)
         { return call_user_func_array($cav,$arg); }
      // --------------------------------------------------------------------------------

      // halt if property
      // --------------------------------------------------------------------------------
         if ($tpe !== nul)
         { throw new Exception('"'.$key.'" is not a function'); }
      // --------------------------------------------------------------------------------

      // try get defined module path.file, else halt: undefined
      // --------------------------------------------------------------------------------
         $pth = self::$crp.'/'.$cls.'/'.str_replace('.', '/', $key);
         $pth = (is_dir(CWD.'/'.$pth) ? ($pth.'/_init.php') : ($pth.'.php'));

         if (!is_readable(CWD.'/'.$pth))
         {
            $msg = (!file_exists(CWD.'/'.$pth) ? 'undefined' : 'forbidden');
            fail::exception('"'.$pth.'" is '.$msg, 'sys/core/extClass.php', 233);
         }

         require($pth);

         $cav = map::get(self::$atr, $key);
         $tpe = typeOf($cav);

         if ($tpe === fnc)
         { return call_user_func_array($cav,$arg); }

         fail::exception
         ((
            ($tpe !== nul)
            ? '"'.$key.'" is not a function'
            : 'set::{"'.$cls.'.'.$key.'"}(function(){}); expected in: "'.$pth.'"'
         ), 'sys/core/extClass.php', 244);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------
/***/
   }
// --------------------------------------------------------------------------------------

?>
