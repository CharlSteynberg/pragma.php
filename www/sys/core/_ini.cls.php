<?

// cls :: core - class definition
// --------------------------------------------------------------------------------------
   class core
   {
   // pty :: attr - `core` attributes
   // -----------------------------------------------------------------------------------
      private static $attr = 0;
   // -----------------------------------------------------------------------------------



   // fnc :: ini - initialize `core`
   // -----------------------------------------------------------------------------------
      public static function ini($lne)
      {
      // def :: local - vars
      // --------------------------------------------------------------------------------
         $cfd = 'sys/core';                                    // core file directory
         $ccp = 'cfg/core/_ini.cfg.jso';                       // core config path
         $cfg = json_decode(file_get_contents($ccp));          // core config object
         $cml = $cfg->modeList;                                // core mode list
         $cfl = scandir($cfd);                                 // core file list
      // --------------------------------------------------------------------------------


      // cnd :: fail - on invalid conf
      // --------------------------------------------------------------------------------
         if ($cfg === null)
         { core::fail('config - invalid JSON syntax', $ccp, '?'); }

         if (!in_array($cfg->coreMode, $cml))
         { core::fail('config - invalid `coreMode`',$ccp,3); }
      // --------------------------------------------------------------------------------


      // def :: global - constants
      // --------------------------------------------------------------------------------
         foreach ($cfg->constant as $key => $val)
         { define($key,$val); }

         $cwd = getcwd();
         $shd = explode('/', $cwd);

         define('CWD', "$cwd/");                            // current working directory
         define('SHD',array_pop($shd));                     // system home directory
         define('ENC',$cfg->encoding);                      // system wide charset
         define('core', CRB.'core'.CRE);                    // class reference constant
         define('MODE',$cfg->coreMode);                     // core runtime mode
      // --------------------------------------------------------------------------------


      // run :: loop - on `$cfl` & require conditionally
      // --------------------------------------------------------------------------------
         foreach ($cfl as $itm)
         {
            if (($itm[0] !== '.') && ($itm[0] !== '_'))
            { require_once("$cfd/$itm"); }
         }
      // --------------------------------------------------------------------------------


      // set :: attr - `core` attributes object
      // --------------------------------------------------------------------------------
         self::$attr = obj
         ([
            'conf' =>$cfg,
            'scope'=>$cfg->atrScope,
            'stack'=>[],
            'paths'=>[]
         ]);
      // --------------------------------------------------------------------------------


      // set :: conf - interals
      // --------------------------------------------------------------------------------
         error_reporting(0);                                // prevent double error mesg
         mb_internal_encoding(ENC);                         // encoding (charset)
         date_default_timezone_set($cfg->timeZone);         // time-zone
      // --------------------------------------------------------------------------------


      // set :: auto - class loading
      // --------------------------------------------------------------------------------
         spl_autoload_register
         (
            function($cls)
            { self::load($cls); }
         );
      // --------------------------------------------------------------------------------


      // add :: stack - ini
      // --------------------------------------------------------------------------------
         core::stack
         ([
            'file'=>__FILE__,
            'line'=>$lne,
            'call'=>'core::ini',
            'args'=>[$lne]
         ]);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: stack - build clean stack trace
   // -----------------------------------------------------------------------------------
      public static function stack($add=null)
      {
      // cnd :: add - prepend stack item
      // --------------------------------------------------------------------------------
         if ($add !== null)
         {
            $stc = self::$attr->stack;
            $pth = self::$attr->paths;
            $add = (object)$add;

            $add->file = str_replace(CWD, '', $add->file);

            array_unshift($stc,$add);
            array_unshift($pth,$add->file);

            self::$attr->stack = $stc;
            self::$attr->paths = $pth;

            return true;
         }
      // --------------------------------------------------------------------------------


      // def :: locals
      // --------------------------------------------------------------------------------
         $x = self::$attr->stack;
         $s = debug_backtrace();
         $n = count($x);
         $m = self::$attr->conf->stackMax;
      // --------------------------------------------------------------------------------


      // cnd :: fail - on stack limit
      // --------------------------------------------------------------------------------
         if ($n >= $m)
         {
         // add :: fail - to stack
         // --------------------------------------------------------------------------------
            $err = 'stack overflow';
            $msg = "Maximum call stack limit ($m) exceeded!";

            core::stack
            ([
               'file'=>(isset($z['file']) ? $z['file'] : __FILE__),
               'line'=>(isset($z['line']) ? $z['line'] : __LINE__),
               'call'=>"fail::$err",
               'args'=>[$msg]
            ]);

            fail::{$err}($msg);
         // --------------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------


      // cnd :: skip - on useless stack, else set $v to 2nd stack item
      // --------------------------------------------------------------------------------
         if (count($s) < 2)
         { return false; }
         else
         { $i = $s[1]; }

         if (!isset($i['file']) || !isset($i['line']) || !$i['line'] || !isset($i['function']))
         { return false; }
      // --------------------------------------------------------------------------------


      // def :: name - class & function
      // --------------------------------------------------------------------------------
         $c = (isset($i['class']) ? $i['class'] : 'call');
         $f = $i['function'];
      // --------------------------------------------------------------------------------


      // cnd :: shift - stack item
      // --------------------------------------------------------------------------------
         if (defined($c) && ($f === 'set') && (count($s) > 2))
         {
            $i = $s[2];
            $a = $i['args'][0];
            $q = ["set::{'".$a."'}", 'set::{"'.$a.'"}'];
            $z = path::find($q, $i['file']);
            $z = ($z ? $z : []);

            $i['args'][0] = str_replace("$c.", '', $i['args'][0]);
            $i['line'] = (isset($z[1]) ? $z[1] : $i['line']);
         }
      // --------------------------------------------------------------------------------


      // def :: local - stack vars
      // --------------------------------------------------------------------------------
         $p = str_replace(CWD, '', $i['file']);
         $l = $i['line'];
         $a = $i['args'];
      // --------------------------------------------------------------------------------


      // cnd :: filter - for cleaner stack
      // --------------------------------------------------------------------------------
         if
         (
            (($c === 'core') && ($f === 'get'))
         )
         { return false; }
      // --------------------------------------------------------------------------------


      // cnd :: edit - stack item
      // --------------------------------------------------------------------------------
         if ($f === '__callStatic')
         {
            $f = $a[0];
            $a = $a[1];

            if (mb_strlen($f) > 30)
            { $f = mb_substr(0,27).'...'; }

            $f = SRB.$f.SRE;
         }
      // --------------------------------------------------------------------------------


      // set :: stack - one item
      // --------------------------------------------------------------------------------
         core::stack
         ([
            'file'=>$p,
            'line'=>$l,
            'call'=>"$c::$f",
            'args'=>$a
         ]);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: fail - halt on core fail with debug template html
   // -----------------------------------------------------------------------------------
      public static function fail($m,$f,$l)
      {
         $msg = 'CORE ERROR&nbsp;&nbsp;'.$m;
         $htm = file_get_contents('cfg/http/tpl/dbug.tpl.htm');
         $stc = ['<tr>','',"</tr>\n"];
         $lst = ['1','core::init','[]',$f,"($l)"];

         foreach ($lst as $idx => $itm)
         { $stc[1] .= '<td id="col'.$idx.'">'.$itm.'</td>'; }

         $stc = implode($stc);
         $htm = str_replace(['({msg})','({dbg})','({stc})'], [$msg,'...',$stc], $htm);

         echo $htm;
         exit(1);
      }
   // -----------------------------------------------------------------------------------



   // fnc :: load - classes, extensions & configuration from path by reference
   // -----------------------------------------------------------------------------------
      public static function load($ref=udf)
      {
      // add :: to - call-stack
      // --------------------------------------------------------------------------------
         self::stack();
      // --------------------------------------------------------------------------------

      // cnd :: dbug - `$ref`
      // --------------------------------------------------------------------------------
         dbug::type($ref,str);
      // --------------------------------------------------------------------------------

      // def :: local - vars
      // --------------------------------------------------------------------------------
         $pts = explode('.',$ref);
         $cnt = count($pts);
         $cls = array_shift($pts);
         $rpn = (file_exists(CWD."sys/$cls") ? "sys/$cls" : "app/php/$cls");
         $cpn = (($rpn === "sys/$cls" ? "cfg/$cls" : "app/cfg/$cls"));

         $lri = array_pop($pts);
         $erp = implode($pts,'/');
         $cep = (isset($pts[0]) ? $pts[0] : $lri);

         $erp = ($erp ? "/$erp" : '');
         $lri = ($lri ? "/$lri" : '');
         $cep = ($cep ? "/$cep" : '');

         $pnl = obj
         ([
            'cls'=>"$rpn/_ini.cls.php",
            'fnc'=>"$rpn$erp$lri.fnc.php",
            'ifl'=>"$rpn/func.lib.php",
            'efl'=>"$rpn$erp/func.lib.php",
            'icp'=>"$cpn/_ini.cfg.jso",
            'ecp'=>"$cpn$cep.cfg.jso",
         ]);
      // --------------------------------------------------------------------------------

      // cnd :: update - `$pnl` according to ref parts
      // --------------------------------------------------------------------------------
         if ($cnt < 2)
         {
            if (defined($cls))
            { return true; }

            unset($pnl->fnc, $pnl->ifl, $pnl->efl, $pnl->ecp);
         }
         else
         {
            $fin = (($cnt < 3) ? ltrim($lri,'/') : $pts[0]);
            $rpn = "$cls/$fin";

            if ($cnt < 3)
            {
               $pnl->fnc = (file_exists(CWD.$pnl->ifl) ? $pnl->ifl : $pnl->fnc);

               unset($pnl->ifl, $pnl->efl, $pnl->ecp);
            }
            else
            {
               $pnl->efl = (file_exists(CWD.$pnl->ifl) ? $pnl->ifl : $pnl->efl);
               $pnl->fnc = (file_exists(CWD.$pnl->efl) ? $pnl->efl : $pnl->fnc);

               if (strpos($pnl->fnc, "$cls/$pts[0]/") === false)
               { unset($pnl->ecp); }

               unset($pnl->ifl, $pnl->efl);
            }
         }
      // --------------------------------------------------------------------------------

      // run :: loop - on `$pln`
      // --------------------------------------------------------------------------------
         foreach ($pnl as $key => $pth)
         {
            if (!file_exists(CWD.$pth) && (MODE === 'devl') && defined('devl'))
            {
               devl::make($pth);
            }

            $inf = path::info($pth);

            if ($inf->stat === 200)
            {
               if ($inf->extn === 'php')
               {
                  require_once(CWD.$pth);

                  if (method_exists($cls, 'ini') && !defined($cls))
                  {
                     define($cls, CRB.$cls.CRE);
                     $cls::ini();
                  }

                  continue;
               }

               if ($inf->extn === 'jso')
               {
                  // $ref = ((strpos($pth,'_ini.cfg') !== false) ? 'conf' : 'conf.'.$pts[0]);
                  // debug('todo !! make config with: '.$ref);
                  // $cls::set($ref, path::read($pth,auto));
               }
            }
         }
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: tst - check if `core.attr.ref` is valid
   // -----------------------------------------------------------------------------------
      public static function tst($ref)
      {
      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $cls = __CLASS__;
         $lst = explode('.',$ref);
         $ref = (($lst[0] === $cls) ? "$cls.$ref" : $ref);
         $pth = self::$attr->paths[0];
         $stc = self::$attr->stack[0];
         $fnc = explode('::', $stc->call)[1];
         $scp = self::$attr->scope;
         $arr = [];
      // --------------------------------------------------------------------------------

      // cnd :: fail - if `template` is map's first item, on if `$ref` is invalid
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



   // fnc :: set - define `core` attribute by ref
   // -----------------------------------------------------------------------------------
      public static function set($ref,$val)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         self::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------

      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $pth = self::$attr->paths[0];
         $scp = get::{'*:'}(self::$attr->conf->atrScope,Keys);
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



   // fnc :: get - `core` attribute by ref
   // -----------------------------------------------------------------------------------
      public static function get($ref,$dat=udf)
      {
      // add :: to - call-stack
      // --------------------------------------------------------------------------------
         if (!defined('failMode')){ self::stack(); }
      // --------------------------------------------------------------------------------

      // run :: test - `$ref`
      // --------------------------------------------------------------------------------
         self::tst($ref);
      // --------------------------------------------------------------------------------

      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $rsl = get::{$ref}(self::$attr,$dat);

      // TODO !! `once` scope !!
      // --------------------------------------------------------------------------------


      // cnd :: on - undefined `$rsl` & ref is `conf.*`
      // --------------------------------------------------------------------------------
         if ($rsl === udf)
         {
            if (substr($ref,0,5) === 'conf.')
            { core::load(__CLASS__.'.'.$ref); }

            $rsl = get::{$ref}(self::$attr,$dat);
         }
      // --------------------------------------------------------------------------------


      // rsl :: value
      // --------------------------------------------------------------------------------
         return $rsl;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: add - extend `core.attr` by ref; create if not exist
   // -----------------------------------------------------------------------------------
      public static function add($ref,$val)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         self::stack();
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



   // fnc :: rip - delete `core` attribute by ref
   // -----------------------------------------------------------------------------------
      public static function rip($ref)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         self::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------

      // set :: attr - to updated value
      // --------------------------------------------------------------------------------
         self::$attr = rip::{$ref}(self::$attr);
         return true;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------



   // fnc :: has - check if `core` has attribute by ref
   // -----------------------------------------------------------------------------------
      public static function has($ref)
      {
      // add :: to - call-stack
      // --------------------------------------------------------------------------------
         self::stack();
      // --------------------------------------------------------------------------------

         return ((get::{$ref}(self::$attr) !== udf) ? true : false);
      }
   // -----------------------------------------------------------------------------------



   // fnc :: call - `core` func by ref (if function name is not pre-defined)
   // -----------------------------------------------------------------------------------
      public static function __callStatic($ref, $arg)
      {
      // run :: stack & test
      // --------------------------------------------------------------------------------
         self::stack();
         self::tst($ref);
      // --------------------------------------------------------------------------------


      // def :: vars - locals
      // --------------------------------------------------------------------------------
         $pty = self::get($ref);
         $tpe = typeOf($pty);
      // --------------------------------------------------------------------------------


      // cnd :: type - `fnc`
      // --------------------------------------------------------------------------------
         if ($tpe === fnc)
         { return call_user_func_array($pty,$arg); }
      // --------------------------------------------------------------------------------


      // run :: fail - ref
      // --------------------------------------------------------------------------------
         fail::{Fat}("`core.$ref` is ".(($tpe === udf) ? Udf : Unc));
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// run :: core.ini - start core
// --------------------------------------------------------------------------------------
   core::ini(__LINE__);
// --------------------------------------------------------------------------------------


// cnd :: mode - continue ONLY if core-mode is NOT `none`
// --------------------------------------------------------------------------------------
   if (MODE !== 'none')
   {
   // cnd :: mode - load `devl` class if core-mode is `devl`
   // -----------------------------------------------------------------------------------
      if (MODE === 'devl')
      {
         core::load(MODE);
      }
   // -----------------------------------------------------------------------------------

   // run :: load - http
   // -----------------------------------------------------------------------------------
   // http::render(204);

      core::load('ater.sambi.dee');
   // -----------------------------------------------------------------------------------
echo 'done!';
   // exit :: clean
   // -----------------------------------------------------------------------------------
      exit(0);
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------

?>
