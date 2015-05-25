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


      // tag :: name & first key value
      // --------------------------------------------------------------------------------
         reset($nde);
         $tag = key($nde);
         $fkv = ((($nde->$tag === null) && isset($nde->src)) ? '' : $nde->$tag);

         if (!is::str($fkv))
         {
            if (isset($nde->src))
            { throw new Exception('invalid node structure'); }

            $nde->src = $fkv;
            $fkv = '';
         }

         $fkv = trim($fkv);
         $nde->$tag = $fkv;
      // --------------------------------------------------------------------------------


      // cst :: custom nodes
      // --------------------------------------------------------------------------------
         $cst = false;

         if (strpos('head title meta style script body', $tag) === false)
         {
         // custom locals
         // -----------------------------------------------------------------------------
            $cns = 'pub/obj/'.$tag.'.jsam';
            $ico = (file_exists($cns) ? true : false);
            $cst = ($ico ? 'div' : (isset($cfg->ndeAlias->$tag) ? $cfg->ndeAlias->$tag : false));
         // -----------------------------------------------------------------------------

         // set cast
         // -----------------------------------------------------------------------------
            if ($cst && !isset($nde->cast))
            {
               if ($ico)
               { $nde->cast = 'auto'; }

               $fca = (((strlen($fkv) > 1) && (($fkv[0] === '#') || ($fkv[0] === '.'))) ? true : false);

               if ((strlen($fkv) < 1) || ($fca === true))
               {
                  $fkv = trim(".$tag .$tag-auto $fkv");
               }
               else
               {
                  $hqa = (((strpos($fkv, ' #') !== false) || (strpos($fkv, ' .') !== false)) ? true : false);

                  if (($hqa === false) && (strpos($fkv, ' ') === false) && isset($vrs->{'$CSS'}->$tag))
                  {
                     if ($ico)
                     { $nde->cast = $fkv; }

                     $fkv = ".$tag .$tag-$fkv";
                  }
                  else
                  {
                     if ($hqa === true)
                     {
                        $pts = explode(' ', $fkv);
                        $ocn = array_shift($pts);

                        if ($ico)
                        { $nde->cast = $ocn; }

                        $fkv = ".$tag .$tag-$ocn ".implode($pts, ' ');
                     }
                     else
                     {
                        $nde->src = $fkv;
                        $fkv = ".$tag .$tag-auto";
                     }
                  }
               }

               // if ($tag == 'note')
               // {
               //
               // }

               // if (!$ico)
               // { unset($nde->cast); }
            }
         // -----------------------------------------------------------------------------

         // set variables
         // -----------------------------------------------------------------------------
            $vrs->{'this'} = null;
            $vrs->{'this'} = new obj();

            if (isset($vrs->{'$form'}))
            { $vrs->{'this'}->form = $vrs->{'$form'}; }

            // if (!isset($vrs->{'this'}))
            // { $vrs->{'this'} = new obj(); }
            //
            // $vrs->{'this'}->src = null;
            // foreach ($vrs->{'this'} as $vn => $vv)
            // { $vrs->{'this'}->$vn = null; }

            foreach ($nde as $atr => $pty)
            {
               if ($atr === $tag)
               { continue; }

               $vrs->{'this'}->$atr = $pty;
            }
         // -----------------------------------------------------------------------------

         // handle source
         // -----------------------------------------------------------------------------
            if ($ico)
            {
               $nde->src = parse::file($cns, $vrs)->{'text/html'};
            }
         // -----------------------------------------------------------------------------

            $nde->$tag = $fkv;

         }
      // --------------------------------------------------------------------------------



      // atr :: quick
      // --------------------------------------------------------------------------------
         if ((typeOf($nde->$tag) === str) && (strlen($nde->$tag) > 0))
         {
            $v = trim($nde->$tag);

            if ((strlen($v) > 0) && (($v[0] === '#') || ($v[0] === '.')))
            {
               $p = explode(' ', $v);

               foreach ($p as $i => $a)
               {
                  if (strlen($a) < 2)
                  { continue; }

                  $a = trim($a);
                  $v = substr($a, 1, length($a));

                  if ($a[0] === '#')
                  {
                     $nde->id = $v;
                     $nde->name = $v;

                     if ($tag === 'form')
                     { $vrs->{'$form'} = $v; }
                  }
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
            {
               $nde->src = $v;
               $nde->$tag = null;
            }
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
         $otn = $tag;

         $tag = (($cst !== false) ? $cst : $tag);
         $tag = (isset($cfg->ndeAlias->$tag) ? $cfg->ndeAlias->$tag : $tag);

         $rsl .= $nlc.$ind.'<'.$tag;

         foreach($nde as $k => $v)
         {
            $t = typeOf($v);

            if (($k != $tag) && ($t !== arr) && ($t !== obj))
            {
               $v = to::str($v);

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
                  if ($k === $otn)
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
                  $str = '';
                  $lns = explode("\n", $nde->src);

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



   // post processing
   // -----------------------------------------------------------------------------------
      if (strpos($rsl, '</pre>') !== false)
      {
         $bgn = (strpos($rsl, '<pre>') + 5);
         $end = strpos($rsl, '</pre>');
         $len = ($end - $bgn);
         $txt = substr($rsl, $bgn, $len);

         $lns = str_replace("\t", ' ', trim($txt));
         $lns = explode("\n", $lns);
         $str = '';

         foreach ($lns as $l)
         { $str .= trim($l)."\n"; }

         $str = highlight_string('<?'.$str.'?>', true);
         $str = str_replace(['&lt;?', '?&gt;', '<code>', '</code>'], '', $str);
         $str = str_replace('#FF8000', '#AAA', $str);
         $str = '<div class="code">'.$str.'</div>';

         $rsl = substr_replace($rsl, $str, ($bgn -5), ($len + 11));
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
   // render
   // -----------------------------------------------------------------------------------
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
   // -----------------------------------------------------------------------------------


   // return result
   // -----------------------------------------------------------------------------------
      return $rsl;
   // -----------------------------------------------------------------------------------
   });
// --------------------------------------------------------------------------------------

?>
