<?

// fail:: unified error handler
// --------------------------------------------------------------------------------------
   class fail
   {
   // _code :: error names
   // -----------------------------------------------------------------------------------
      public static function _code($n)
      {
         $n = ($n.'');
         $e = // array
         [
            '2'    => 'warning',
            '8'    => 'notice',
            '32'   => 'warning',
            '128'  => 'warning',
            '512'  => 'warning',
            '1024' => 'notice',
            '2024' => 'strict',
            '8192' => 'deprecated',
            '16384'=> 'deprecated',
         ];

         return (isset($e[$n]) ? $e[$n] : 'fatal');
      }
   // -----------------------------------------------------------------------------------


   // fail :: {error name} (`error message`, `var to string`)
   // -----------------------------------------------------------------------------------
      public static function __callStatic($nme, $atr)
      {
      // def :: fail - unstable
      // --------------------------------------------------------------------------------
         if (!defined('failMode'))
         { define('failMode',true); }
      // --------------------------------------------------------------------------------


      // locals
      // --------------------------------------------------------------------------------
         $err = ((isset($atr[1]) || (strpos($nme, ' ') !== false)) ? $nme : "$nme ERROR");
         $nme = strtoupper((strlen($err) > 0) ? $err : Und);         // error name
         $msg = $nme.'&nbsp;&nbsp;'.(isset($atr[0]) ? $atr[0] : Und);// error message
         $dbg = (isset($atr[1]) ? $atr[1]."...\n\n" : '...');        // to string
         $dbg = str_replace('\n',"\n", $dbg);

         $stc = core::get('stack');                                  // stack trace
         $cfg = core::get('conf.dbugConf');                          // fail config
         $htm = file_get_contents(CWD.$cfg->dbugTmpl);               // debug template
         $sho = '';                                                  // stack htm output
         $cns = core::get('conf.constant');
         $mbc = [];
         $cnt = count($stc);

         if (!isset($stc[0]) || (substr($stc[0]->call,0,4) !== 'fail'))
         {
            core::stack();

            $stc = get::{'stack'}(core);
            $cnt = count($stc);
         }

         foreach ($cns as $key => $val)
         {
            if (mb_strlen($val) === 1)
            { $mbc[] = $val; }
         }

         $jul = str(get::{'><'}(str(to::str($mbc))->swop(['"',"\n",' '],'')))->chop(',');
      // --------------------------------------------------------------------------------


      // prep stack
      // --------------------------------------------------------------------------------
         foreach ($stc as $num =>$itm)
         {
            if ($num > $cfg->stackSho)
            { break; }

            $itm->args = get::{'><'}(trim(to::str($itm->args)));

            $len = mb_strlen($itm->args);
            $apn = ($len > $cfg->mdlLimit ? '...' : '');

            $itm->args = str($itm->args)->swop($jul,$mbc);
            $itm->args = str($itm->args)->trim('<<', (0 - $cfg->mdlLimit));
            $itm->args = str($itm->args)->swop(["\n",'\n','  ',CWD], '');
            $itm->args = htmlentities($itm->args);
            $nbr = ($cnt - $num);

            $sho .= '<tr>';
            $sho .= '<td id="col0">'.$nbr.'</td>';
            $sho .= '<td id="col1">'.str($itm->call)->trim('<<', (0 - $cfg->lftLimit)).'</td>';
            $sho .= '<td id="col2">('.$itm->args.$apn.')</td>';
            $sho .= '<td id="col3">'.str($itm->file)->trim('<<', (0 - $cfg->rgtLimit)).'</td>';
            $sho .= '<td>&nbsp;&nbsp;'.$itm->line.'</td>';
            $sho .= '</tr>'."\n";
         }
      // --------------------------------------------------------------------------------

      // prep html
      // --------------------------------------------------------------------------------
         $fnt = path::read('cfg/http/fnt/c0d3.woff',str);
         $dbg = str($dbg)->swop($jul,$mbc);
         $htm = str($htm)->swop(['({fnt})','({msg})','({dbg})','({stc})'], [$fnt,$msg,$dbg,$sho]);
      // --------------------------------------------------------------------------------

      // show error
      // --------------------------------------------------------------------------------
         echo $htm;
      // --------------------------------------------------------------------------------

      // exit :: fail
      // --------------------------------------------------------------------------------
         exit(1);
      // --------------------------------------------------------------------------------
      }
   }
// --------------------------------------------------------------------------------------



// divert :: error
// --------------------------------------------------------------------------------------
   set_error_handler
   (
      function ()
      {
      // def :: fail - unstable
      // --------------------------------------------------------------------------------
         if (!defined('failMode'))
         { define('failMode',true); }
      // --------------------------------------------------------------------------------

         $arg = func_get_args();
         $nme = fail::_code($arg[0]);
         $msg = str_replace("'", '`', $arg[1]);

         core::stack
         ([
            'file'=>$arg[2],
            'line'=>$arg[3],
            'call'=>'fail::'.$nme,
            'args'=>[$msg]
         ]);

         fail::{$nme}($msg);
         exit(1);
      }
   );
// --------------------------------------------------------------------------------------



// divert :: exception
// --------------------------------------------------------------------------------------
   set_exception_handler
   (
      function ($e)
      {
      // def :: fail - unstable
      // --------------------------------------------------------------------------------
         if (!defined('failMode'))
         { define('failMode',true); }
      // --------------------------------------------------------------------------------

         fail::{'exception'}($e->getMessage());
         exit(1);
      }
   );
// --------------------------------------------------------------------------------------



// divert :: fatal
// --------------------------------------------------------------------------------------
   register_shutdown_function
   (
   // on shut-down, check if there was an error
   // -----------------------------------------------------------------------------------
      function()
      {
      // get :: last error
      // --------------------------------------------------------------------------------
         $err = error_get_last();
      // --------------------------------------------------------------------------------


      // cnd :: only fail if there was actually an error
      // --------------------------------------------------------------------------------
         if ($err !== null)
         {
         // def :: fail - unstable
         // -----------------------------------------------------------------------------
            if (!defined('failMode'))
            { define('failMode',true); }
         // -----------------------------------------------------------------------------

         // def :: locals
         // -----------------------------------------------------------------------------
            $nme = fail::_code($err['type']);
            $msg = str_replace("'", '`', $err['message']);
         // -----------------------------------------------------------------------------


         // set :: stack - add fail to stack-trace
         // -----------------------------------------------------------------------------
            core::stack
            ([
               'file'=>$err['file'],
               'line'=>$err['line'],
               'call'=>'fail::fatal',
               'args'=>[$msg]
            ]);
         // -----------------------------------------------------------------------------

         // rsl :: proceed
         // -----------------------------------------------------------------------------
            fail::{$nme}($msg);
            exit(1);
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------
      }
   );
// --------------------------------------------------------------------------------------

?>
