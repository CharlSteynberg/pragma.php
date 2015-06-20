<?

// def :: core class
// --------------------------------------------------------------------------------------
   class core
   {
   // pty :: locals
   // -----------------------------------------------------------------------------------
      private static $attr = 0;                              // attributes
      private static $tmpl = 0;                              // class template text
   // -----------------------------------------------------------------------------------


   // fnc :: init - initialize core
   // -----------------------------------------------------------------------------------
      public static function fail($m,$f,$l)
      {
         $msg = 'CORE ERROR&nbsp;&nbsp;'.$m;
         $htm = file_get_contents(CWD.'cfg/core/fail.tpl.htm');
         $sho = '';

         $sho .= '<tr>';
         $sho .= '<td id="col0">1</td>';
         $sho .= '<td id="col1">core::init</td>';
         $sho .= '<td id="col2">[]</td>';
         $sho .= '<td id="col3">'.$f.'</td>';
         $sho .= '<td>('.$l.')</td>';
         $sho .= '</tr>'."\n";

         $htm = str_replace(['({msg})','({dbg})','({stc})'], [$msg,'...',$sho], $htm);

         echo $htm;
         exit(1);
      }
   // -----------------------------------------------------------------------------------


   // fnc :: init - initialize core
   // -----------------------------------------------------------------------------------
      public static function init()
      {
      // def :: local - vars
      // --------------------------------------------------------------------------------
         $cfd = 'sys/core';                                    // core file directory
         $ccp = 'cfg/core/_auto.cfg.jso';                      // core config path

         $cfg = json_decode(file_get_contents($ccp));          // core config object
         $cml = $cfg->modeList;                                // core mode list

         $acl = ['siteMode','encoding','language','autoExec']; // apache config list
         $hta = file_get_contents('cfg/core/htaccess.tpl.txt');// htaccess text
      // --------------------------------------------------------------------------------


      // cnd :: fail - on invalid conf
      // --------------------------------------------------------------------------------
         if ($cfg === null)
         { core::fail('config - invalid JSON syntax', $ccp, '?'); }

         if (!in_array($cfg->coreMode, $cml))
         { core::fail("config - invalid `coreMode`;  options are: ".implode($cml,','),$ccp,'?'); }
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


      // cnd :: update - htaccess only if core mode is not `live`
      // --------------------------------------------------------------------------------
         if (MODE !== 'live')
         {
            $cfg->siteMode = ((MODE === 'none') ? 'Off' : 'On');

            foreach ($acl as $itm)
            { $hta = str_replace('({'.$itm.'})', $cfg->{$itm}, $hta); }

            file_put_contents('./.htaccess', $hta);
         }
      // --------------------------------------------------------------------------------


      // run :: require - core files
      // --------------------------------------------------------------------------------
         $sys = opendir($cfd);                              // dir handler

         while ($itm = readdir($sys))
         {
            if (is_file("$cfd/$itm") && ($itm[0] !== '_'))    // only files (non-auto)
            { require_once("$cfd/$itm"); }
         }

         closedir($sys);
      // --------------------------------------------------------------------------------


      // set :: auto - class loading
      // --------------------------------------------------------------------------------
         spl_autoload_register
         (
            function($cls)
            { self::load($cls); }
         );
      // --------------------------------------------------------------------------------


      // set :: self - properties
      // --------------------------------------------------------------------------------
         self::$attr = obj();                               // attrinutes object
         self::$attr->conf = $cfg;                          // config
         self::$attr->stack = [];                           // set call-stack
      // --------------------------------------------------------------------------------


      // set :: conf - interals
      // --------------------------------------------------------------------------------
         error_reporting(0);                                // prevent double error mesg
         mb_internal_encoding(ENC);                         // encoding (charset)
         date_default_timezone_set($cfg->timeZone);         // time-zone
      // --------------------------------------------------------------------------------


      // run :: core - stack
      // --------------------------------------------------------------------------------
         self::stack();
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
            $add = (object)$add;

            $add->file = str_replace(CWD, '', $add->file);

            array_unshift($stc,$add);
            self::$attr->stack = $stc;

            return true;
         }
      // --------------------------------------------------------------------------------


      // def :: locals
      // --------------------------------------------------------------------------------
         $s = debug_backtrace();
         $n = count($s);
         $m = self::$attr->conf->stackMax;
         $r = [];
         $c = [];
      // --------------------------------------------------------------------------------


      // cnd :: skip on empty stack
      // --------------------------------------------------------------------------------
         if ($n < 1)
         { return false; }
      // --------------------------------------------------------------------------------


      // cnd :: fail - on stack limit
      // --------------------------------------------------------------------------------
         if ($n > $m)
         {
         // add :: fail - to stack
         // --------------------------------------------------------------------------------
            $msg = "Maximum call stack limit ($m) exceeded!";

            core::stack
            ([
               'file'=>__FILE__,
               'line'=>__LINE__,
               'call'=>'fail::stack overflow',
               'args'=>[$msg]
            ]);

            fail::{'fatal'}($msg);
         // --------------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------


      // set :: loop - frisk stack
      // --------------------------------------------------------------------------------
         foreach ($s as $k => $v)
         {
         // cnd :: skip - if `no file` or `no line`
         // -----------------------------------------------------------------------------
            if (!isset($v['file']) || !isset($v['line']))
            { continue; }
         // -----------------------------------------------------------------------------

         // set :: path, line, func
         // -----------------------------------------------------------------------------
            $p = str_replace(CWD, '', $v['file']);
            $l = $v['line'].'';
            $f = (isset($v['function']) ? $v['function'] : '_');
         // -----------------------------------------------------------------------------

         // cnd :: if no class, set to `func`
         // -----------------------------------------------------------------------------
            if (!isset($v['class']))
            { $v['class'] = 'func'; }
         // -----------------------------------------------------------------------------

         // cnd :: skip if empty line, empty -or system function, or stack function
         // -----------------------------------------------------------------------------
            if
            (
               ($l === '') ||
               ($f[0] === '_') ||
               ($f === 'spl_autoload_call') ||
               (($v['class'] === 'core') && ($v['function'] === 'stack')) ||
               (($v['class'] === 'core') && ($v['function'] === 'get'))
            )
            { continue; }
         // -----------------------------------------------------------------------------

         // set :: call-paths, short path name, arr to obj, unset function & class
         // -----------------------------------------------------------------------------
            $c[] = $p;
            $v['file'] = $p;

            $v = (object)$v;
            $a = $v->args;

            $v->call = $v->class.'::'.$v->function;
            unset($v->function, $v->class, $v->type, $v->args);
            $v->args = $a;

            $r[] = $v;
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------


      // cnd :: skip on empty stack
      // --------------------------------------------------------------------------------
         if (count($r) < 1)
         { return false; }
      // --------------------------------------------------------------------------------


      // set :: call-paths & stack
      // --------------------------------------------------------------------------------
         self::$attr->paths = $c;
         self::$attr->stack = $r;

         return true;
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------


   // fnc :: load - class loader
   // -----------------------------------------------------------------------------------
      public static function load($ref)
      {
      // set :: core - update stack
      // --------------------------------------------------------------------------------
         core::stack();
      // --------------------------------------------------------------------------------


      // def :: local - vars
      // --------------------------------------------------------------------------------
         $cfn = '_auto.cls.php';                               // auto file name
         $rfn = '_auto.ext.php';                               // auto file name
         $pts = explode('.',$ref);                             // ref parts
         $num = count($pts);                                   // number of parts
         $cls = array_shift($pts);                             // class name
         $erm = implode($pts,'.');                             // extnd ref map
         $lin = array_pop($pts);                               // last item name
         $mdl = implode($pts,'/');                             // middle path

         $srp = "sys/$cls";                                    // sys ref path
         $arp = "pub/app/$cls";                                // app ref path
         $dir = (file_exists(CWD.$srp) ? $srp : $arp);         // auto directory

         $scd = "cfg/$cls";                                    // sys cfg dir
         $acd = "pub/cfg/$cls";                                // app cfg dir
         $crd = (file_exists(CWD.$srp) ? $scd : $acd);         // cfg root dir
      // --------------------------------------------------------------------------------


      // def :: class & conf
      // --------------------------------------------------------------------------------
         $csd = rtrim("$dir/".$mdl, '/');                      // class sub dir

         $crp = "$dir/$cfn";                                   // class ref path

         $arp = (($num > 2) ? "$dir/$pts[0]/$rfn" : null);     // auto ref path
         $arp = (($num === 1) ? null : $arp);

         $mrp = (($num > 1) ? "$csd/$lin.fnc.php" : null);     // method ref path
         $mrp = (($mrp && file_exists("$csd/$lin")) ? "$csd/$lin/$rfn" : $mrp);

         $crc = "$crd/_auto.cfg.jso";                          // class root conf
         $sec = (($num >3)? "$crd/$pts[0].cfg.jso": null);     // class endended conf

         $lst = [$crp,$crc,$sec,$arp,$mrp];
         $cor = false;
      // --------------------------------------------------------------------------------


      // cnd :: conf - only
      // --------------------------------------------------------------------------------
         if (isset($pts[0]) && ($pts[0] === 'conf'))
         {
            $pts = explode('.',$erm); array_shift($pts);
            $efp = implode($pts,'/');
            $cpn = "$crd/$efp.cfg.jso";
            $epn = "$dir/$efp.fnc.php";

            if (file_exists(CWD.$epn))
            {
               $cor = true;
               $lst = [$cpn];
            }
         }
      // --------------------------------------------------------------------------------

      // run :: loop - on `$lst`
      // --------------------------------------------------------------------------------
         foreach ($lst as $nbr => $pth)
         {
            if (!$pth || (($nbr < 1) && ($cor === false) && defined($cls)))
            { continue; }

            $pts = explode('.',$pth);

            if ((MODE !== 'live') && !file_exists(CWD.$pth))
            {
               if (MODE === 'devl')
               {
                  $dat = null;

                  if ($pts[2] === 'php')
                  {
                     if ($pts[1] === 'cls')
                     {
                        $dat = file_get_contents(CWD.'cfg/core/class.tpl.php');
                        $dat = str_replace('template', $cls, $dat);
                     }
                     elseif (($pts[1] === 'ext') || ($pts[1] === 'fnc'))
                     {
                        if (($pts[1] === 'ext') && ($nbr === 3) && ($lst[4] !== null))
                        { $dat = ''; }
                        else
                        {
                           $dat = file_get_contents(CWD.'cfg/core/extnd.tpl.php');
                           $dat = str_replace(['tplMap','tplRef','parent'], [$ref,$erm,$cls], $dat);
                        }
                     }

                  }
                  elseif ($pts[2] === 'jso')
                  { $dat = file_get_contents(CWD.'cfg/core/conf.tpl.jso'); }

                  path::make($pth,$dat);
               }
            }

            if (file_exists(CWD.$pth))
            {
               if ($pts[2] === 'php')
               {
                  require_once(CWD.$pth);

                  if ($pts[1] === 'cls')
                  {
                     define($cls, CRB.$cls.CRE);

                     if (method_exists($cls, 'ini'))
                     { $cls::ini(); }
                  }
               }
               elseif ($pts[2] === 'jso')
               {
                  $crm = array_pop((explode('/',$pts[0])));
                  $crm = (($crm === '_auto') ? 'conf' : "conf.$crm");

                  $cls::add($crm, path::read($pth,auto));
               }
            }
         }
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------


   // get :: compatibility
   // -----------------------------------------------------------------------------------
      public static function get($cmd,$dat=null)
      {
      // cnd :: stack - if `$cmd` is "stack"
      // --------------------------------------------------------------------------------
         if (($cmd === 'stack') && (!defined('failMode')))
         { core::stack(); }
      // --------------------------------------------------------------------------------

      // rsl :: return - get from local `$attr`
      // --------------------------------------------------------------------------------
         return get::{$cmd}(self::$attr,$dat);
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------


   // set :: compatibility
   // -----------------------------------------------------------------------------------
      public static function set($map,$val)
      {
         self::$attr = set::{$map}(self::$attr,$val);
         return true;
      }
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// run :: initiatlize `core`, `path`, `http`
// --------------------------------------------------------------------------------------
   core::init();        // init core
   // core::load('http');  // init http
// --------------------------------------------------------------------------------------


   // fara::{'flerb.bark.loud'}('kazi !');

   $inf = path::read('cfg/http/sys/robots.txt.jso',auto);
   debug($inf);

   echo 'done :)';

// exit :: clean
// --------------------------------------------------------------------------------------
   exit(0);
// --------------------------------------------------------------------------------------

?>
