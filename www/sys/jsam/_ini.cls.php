<?

// cls :: jsam - class definition
// --------------------------------------------------------------------------------------
   class jsam
   {
   // pty :: attr - `jsam` attributes
   // -----------------------------------------------------------------------------------
      private static $attr = 0;
   // -----------------------------------------------------------------------------------



   // fnc :: ini - initialize `jsam`
   // -----------------------------------------------------------------------------------
      public static function ini()
      {
      // set :: attr - `jsam` attributes object
      // --------------------------------------------------------------------------------
         self::$attr = obj(['scope'=>core::get('conf.atrScope')]);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: tst - check if `jsam.attr.ref` is valid
   // -----------------------------------------------------------------------------------
      public static function tst($ref)
      {
      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $cls = __CLASS__;
         $lst = explode('.',$ref);
         $ref = (($lst[0] === $cls) ? "$cls.$ref" : $ref);
         $pth = core::get('paths')[0];
         $stc = core::get('stack')[1];
         $fnc = explode('::', $stc->call)[1];
         $scp = self::$attr->scope;
         $arr = [];
      // --------------------------------------------------------------------------------

      // cnd :: fail - if `jsam` is map's first item, on if `$ref` is invalid
      // --------------------------------------------------------------------------------
         if (($lst[0] === $cls) || ($ref[0] === '.') || (substr($ref,-1,1) === '.'))
         { fail::{Ref}("invalid reference: `$ref`"); }
      // --------------------------------------------------------------------------------

      // run :: loop - on map items
      // --------------------------------------------------------------------------------
         foreach ($lst as $itm)
         {
            $arr[] = $itm;
            $tgt = implode($arr, '.');

            if (isset($scp->bias->$tgt) && ($scp->bias->$tgt !== $pth))
            { fail::{'scope'}("`$cls.$tgt` is biased to `$pth`"); }

            if (isset($scp->lock->$tgt) && str($fnc)->is(['set','add','rip']))
            { fail::{'scope'}("`$cls.$tgt` is locked"); }
         }
      // --------------------------------------------------------------------------------

      // rsl :: true
      // --------------------------------------------------------------------------------
         return true;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: set - define `jsam` attribute by ref
   // -----------------------------------------------------------------------------------
      public static function set($ref,$val)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         core::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------

      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $pth = core::get('paths')[0];
         $scp = get::{'*:'}(core::get('conf.atrScope'),Keys);
      // --------------------------------------------------------------------------------

      // cnd :: set - scope if `$val` is `scope` reference
      // --------------------------------------------------------------------------------
         if ((is::str($val)) && str($val)->is($scp))
         {
            $dat = (str($val)->is([lock,once]) ? true : $pth);
            self::$attr->scope->{$val}->$ref = $dat;
            return true;
         }
      // --------------------------------------------------------------------------------

      // set :: attr - map value
      // --------------------------------------------------------------------------------
         self::$attr = set::{$ref}(self::$attr,$val);
         return true;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: get - `jsam` attribute by ref
   // -----------------------------------------------------------------------------------
      public static function get($ref,$dat=udf)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         core::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------

      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $rsl = get::{$ref}(self::$attr,$dat);

      // TODO !! `once` scope !!
      // --------------------------------------------------------------------------------


      // cnd :: on - undefined `$rsl` & ref is conf.*
      // --------------------------------------------------------------------------------
         if ($rsl === udf)
         {
            if (substr($ref,0,5) === 'conf.')
            {
               core::load(__CLASS__.'.'.$ref);
               $rsl = get::{$ref}(self::$attr,$dat);
            }
         }
      // --------------------------------------------------------------------------------


      // rsl :: value
      // --------------------------------------------------------------------------------
         return $rsl;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: add - extend `jsam.attr` by ref; create if not exist
   // -----------------------------------------------------------------------------------
      public static function add($ref,$val)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         core::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------

      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $pty = get::{$ref}(self::$attr);
         $pty = (is::udf($pty) ? $val : val::of($pty)->add($val));
      // --------------------------------------------------------------------------------

      // set :: attr - map value
      // --------------------------------------------------------------------------------
         self::$attr = set::{$ref}(self::$attr,$pty);
         return true;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: rip - delete `jsam` attribute by ref
   // -----------------------------------------------------------------------------------
      public static function rip($ref)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         core::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------

      // set :: attr - to updated value
      // --------------------------------------------------------------------------------
         self::$attr = rip::{$ref}(self::$attr);
         return true;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: has - check if `jsam` has attribute by ref
   // -----------------------------------------------------------------------------------
      public static function has($ref)
      {
      // add :: to - call-stack
      // --------------------------------------------------------------------------------
         core::stack();
      // --------------------------------------------------------------------------------

         return ((get::{$ref}(self::$attr) !== udf) ? true : false);
      }
   // -----------------------------------------------------------------------------------



   // fnc :: call - `jsam` func by ref (if function name is not pre-defined)
   // -----------------------------------------------------------------------------------
      public static function __callStatic($ref, $arg)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         core::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------


      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $cls = __CLASS__;
         $pty = get::{$ref}(self::$attr);
         $tpe = typeOf($pty);
      // --------------------------------------------------------------------------------


      // cnd :: fix - for extended functions
      // --------------------------------------------------------------------------------
         if (($tpe !== udf) && ($tpe !== fnc))
         {
            $pts = explode('.',$ref);
            $lmi = array_pop($pts);
            $frs = ltrim(implode($pts,'.').'.'.SRB.$lmi.SRE, '.');
            $pty = get::{$frs}(self::$attr);
            $tpe = typeOf($pty);
         }
      // --------------------------------------------------------------------------------


      // cnd :: type - `fnc`
      // --------------------------------------------------------------------------------
         if ($tpe === fnc)
         { return call_user_func_array($pty,$arg); }
      // --------------------------------------------------------------------------------


      // cnd :: type - `udf`
      // --------------------------------------------------------------------------------
         if ($tpe === udf)
         {
         // run :: load - extension
         // -----------------------------------------------------------------------------
            core::load("$cls.$ref");
         // -----------------------------------------------------------------------------

         // def :: vars - locals
         // -----------------------------------------------------------------------------
            $pty = get::{$ref}(self::$attr);
            $tpe = typeOf($pty);
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------


      // cnd :: type - `fnc`
      // --------------------------------------------------------------------------------
         if ($tpe === fnc)
         { return call_user_func_array($pty,$arg); }
      // --------------------------------------------------------------------------------


      // def :: extension - path
      // --------------------------------------------------------------------------------
         $dir = str_replace(CWD, '', __DIR__);
         $pth = "$dir/".str_replace('.','/',$ref);
         $pth = (file_exists(CWD.$pth) ? ($pth.'/_ini.cls.php') : ($pth.'.fnc.php'));
      // --------------------------------------------------------------------------------


      // run :: fail - ref
      // --------------------------------------------------------------------------------
         if ($tpe === udf)
         { $msg = "`set::{'$cls.$ref'}(function(){});`  expected from: `$pth`"; }
         else
         { $msg = "`$cls.$ref` is not a function"; }

         fail::{'fatal'}($msg);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------

?>
