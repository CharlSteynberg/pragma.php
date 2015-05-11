<?

// recursively build output
// --------------------------------------------------------------------------------------
   function _build_text_html($dfn, $lvl, $cfg, $vrs)
   {
   // buffers
   // -----------------------------------------------------------------------------------
      $rsl = '';
      $ind = '';
      $nlc = '';
      $tab = '';
   // -----------------------------------------------------------------------------------


   // indentation
   // -----------------------------------------------------------------------------------
      if ($cfg->minified === false)
      {
         $nlc = "\n";
         $tab = '   ';

         for ($l=0; $l<$lvl; $l++){ $ind .= $tab; }
      }
   // -----------------------------------------------------------------------------------


   // build node list
   // -----------------------------------------------------------------------------------
      foreach($dfn as $key => $nde)
      {
      // validate
      // --------------------------------------------------------------------------------
         if (!is_int($key) || (typeOf($nde) !== 'object'))
         {
            if (typeOf($nde) === arr)
            { $rsl .= _build_text_html($nde, $lvl, $cfg, $vrs); continue; }
            else
            {
               continue;
               //throw new Exception('numeric array of objects expected');
            }
         }
      // --------------------------------------------------------------------------------

      // tag :: name
      // --------------------------------------------------------------------------------
         $tag = key($nde);
         $cnl = map::get($cfg, 'cstNodes');
         $cst = map::get($cnl, $tag);
         $ctv = null;
         $atr = null;
      // --------------------------------------------------------------------------------

      // custom tag src & vars
      // --------------------------------------------------------------------------------
         if ($cst !== null)
         {
            $atr = map::get($cst, 'atr');
            $cns = map::get($cst, 'src');

            $nde->type = $nde->$tag;

            if (typeOf($atr) === arr)
            {
               foreach ($atr as $avl)
               {
                  $avn = key($avl);
                  $avs = $avl->$avn;

                  if (isset($nde->$avs))
                  {
                     $vrs->$avn = $nde->$avs;
                     unset($nde->$avs);
                  }
               }
            }

            if ($cns !== null)
            {
               $cns = parse::file($cns, $vrs)->{'text/html'};

               if (typeOf($cns) !== arr)
               { $cns = [$cns]; }

               if (isset($nde->src))
               {
                  $src = $nde->src;

                  if (typeOf($src) === str)
                  {
                     if (str::typeOf($src) === pth)
                     {
                        if (path::info($src)->extn === 'jsam')
                        { $src = parse::file($src, $vrs)->{'text/html'}; }
                     }
                     else
                     { $src = [new obj(['span'=>$src])]; }
                  }

                  if (typeOf($src) !== arr)
                  { $src = [$src]; }

                  foreach ($src as $ens)
                  { $cns[] = $ens; }
               }

               $nde->src = $cns;
            }
         }
      // --------------------------------------------------------------------------------

      // atr :: quick
      // --------------------------------------------------------------------------------
         if ((typeOf($nde->$tag) === str) && (strlen($nde->$tag) > 0))
         {
            $v = trim($nde->$tag);

            if ((strlen($v) > 0) && (($v[0] === '#') || ($v[0] === '.') || ($cst !== null)))
            {
               $p = explode(' ', $v);

               foreach ($p as $i => $a)
               {
                  if (($i < 1) && ($cst !== null))
                  { $a = '.'.$cst->pre.$a; }

                  $v = substr($a, 1, length($a));

                  if ($a[0] === '#')
                  { $nde->id = $v; }
                  elseif ($a[0] === '.')
                  {
                     if (!isset($nde->class))
                     { $nde->class = []; }
                     elseif(typeOf($nde->class) === 'string')
                     { $nde->class = explode(' ', $nde->class); }

                     $nde->class[] = $v;
                  }
               }
            }
            else
            { $nde->src = $v; }
         }
         else
         {
            if (length($nde->$tag) > 0)
            { $nde->src = $nde->$tag; }
         }
      // --------------------------------------------------------------------------------


      // atr :: class to str
      // --------------------------------------------------------------------------------
         if (isset($nde->class) && (typeOf($nde->class) === arr))
         { $nde->class = implode($nde->class, ' '); }
      // --------------------------------------------------------------------------------


      // atr :: src == file
      // --------------------------------------------------------------------------------
         if
         (
            isset($nde->src)
            && (typeOf($nde->src) === str)
            && (strpos($nde->src, '/') !== false)
            && (strpos($nde->src, '.') !== false)
            && preg_match('/^[a-zA-Z0-9-\/\._]+$/', $nde->src)
         )
         {
            $sp = $nde->src;
            $sp = (($sp[0] === '/') ? 'pub'.$sp : $sp);

            if (!is_readable(CWD.'/'.$sp))
            {
               $m = (!file_exists(CWD.'/'.$sp) ? 'undefined' : 'forbidden');
               throw new Exception('"'.$nde->src.'" is '.$m);
            }

            $pi = path::info($sp);
            $sn = ((strpos($cfg->srcNodes, ' '.$tag.' ') !== false) ? true : false);
            $bf = $pi->bnry;

            if ($sn && $bf)
            {
               if (($pi->size / 1024) <= $cfg->embdSize)
               { $nde->src = 'data:'.$pi->mime.';base64,'.base64_encode(file_get_contents($sp)); }
               else
               { $nde->src = substr($sp, 3, length($sp)); }
            }
            elseif (!$bf)
            {
               $src = path::read($sp, $vrs)->body;

               if ($pi->extn === 'js')
               {
                  if (liveMode === false)
                  { pack::js($sp); }

                  if ($cfg->minified === true)
                  { $src = path::read('sys/pack/cache/'.str_replace('/','.',$sp))->body; }
               }


               if ($cfg->minified === false)
               {
                  $nde->src = '';
                  $src = explode("\n", $src);

                  foreach ($src as $lne)
                  { $nde->src .= $lne.$nlc; }

                  $nde->src = rtrim($nde->src);
               }
               else
               {
                  $nde->src = $src;
               }
            }
         }
      // --------------------------------------------------------------------------------


      // auto atr
      // --------------------------------------------------------------------------------
         if (isset($cfg->autoAttr->$tag))
         {
            foreach ($cfg->autoAttr->$tag as $aap => $aav)
            {
               $nde->$aap = $aav;
            }
         }
      // --------------------------------------------------------------------------------


      // node :: bgn
      // --------------------------------------------------------------------------------
         $otn  = $tag;
         $tag  = (($cst === null) ? $tag : $cst->tag);
         $rsl .= $nlc.$ind.'<'.$tag;

         foreach($nde as $k => $v)
         {
            if (($k != $tag) && (typeOf($v) === str))
            {
               if ($k === 'src')
               {
                  if
                  (
                     (strpos($cfg->srcNodes, ' '.$tag.' ') !== false) &&
                     (
                        (substr($v, 0, 5) === 'data:') ||
                        (
                           (strpos($nde->src, '/') !== false) &&
                           (strpos($nde->src, '.') !== false) &&
                           preg_match('/^[a-zA-Z0-9-\/\._]+$/', $v)
                        )
                     )
                  )
                  {
                     $rsl .= ' '.$k.'="'.$v.'"';
                     unset($nde->src);
                  }
               }
               else
               {
                  if (($cst !== null) && ($k === $otn))
                  { continue; }

                  $rsl .= ' '.$k.'="'.$v.'"';
               }
            }
            elseif (($k == 'text/css') && ($tag == 'style'))
            {
               $nde->src = jsam::build(new obj([$k=>$v]), $vrs)->body;
            }
         }

         $rsl .= '>';
      // --------------------------------------------------------------------------------

      // skip :: void tag
      // --------------------------------------------------------------------------------
         if (strpos($cfg->voidTags, ' '.$tag.' ') !== false){ continue; }
      // --------------------------------------------------------------------------------

      // node :: src
      // --------------------------------------------------------------------------------
         if (isset($nde->src))
         {
            $t = typeOf($nde->src);

            if (($t === obj) || ($t === arr))
            {
               $lvl++;
               $rsl .= _build_text_html($nde->src, $lvl, $cfg, $vrs);
               $lvl--;
            }
            else
            {
               if (($cfg->minified === true) || (strpos($nde->src, "\n") === false))
               { $rsl .= $nde->src; }
               else
               {
                  $lns = explode("\n", $nde->src);
                  $str = '';

                  foreach ($lns as $lne)
                  { $str .= $ind.$lne.$nlc; }

                  $rsl .= rtrim($str);
               }
            }
         }
      // --------------------------------------------------------------------------------

      // node :: end
      // --------------------------------------------------------------------------------
         if (isset($nde->src) && ((typeOf($nde->src) === arr) || (strpos($nde->src, "\n") !== false)))
         { $rsl .= $nlc.$ind; }

         $rsl .= '</'.$tag.'>';
      // --------------------------------------------------------------------------------
      }
   // -----------------------------------------------------------------------------------

   // return result
   // -----------------------------------------------------------------------------------
      return $rsl;
   // -----------------------------------------------------------------------------------
   }
// --------------------------------------------------------------------------------------



// compiler
// --------------------------------------------------------------------------------------
   jsam::set("build.text.html", function($dfn, $vrs)
   {
      if (!isset($dfn[0]) || (typeOf($dfn[0]) !== 'object'))
      { throw new Exception('numeric array of objects expected'); }

      $cfg = parse::file('cfg/jsam/build.text.html.jsam');

      if ($cfg->minified === 'auto')
      { $cfg->minified = ((liveMode === false) ? false : true); }

      $nlc = (($cfg->minified === false) ? "\n" : '');
      $pfx = '';
      $sfx = '';

      if (strtolower(key($dfn[0])) === 'head')
      {
         $pfx = "<!DOCTYPE html>$nlc<html>";
         $sfx = "$nlc</html>";
      }

      $rsl  = $pfx;
      $rsl .= _build_text_html($dfn, 1, $cfg, $vrs);
      $rsl .= $sfx;

      return $rsl;
   });
// --------------------------------------------------------------------------------------

?>
