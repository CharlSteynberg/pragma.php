<?

// build jsam from file path or parsed context
// --------------------------------------------------------------------------------------
   jsam::set('build', function($dfn, $vrs=null)
   {
   // if path defined :: acquire - with vars
   // -----------------------------------------------------------------------------------
      if ((typeOf($dfn) == str) && (substr($dfn, -4, 4) == 'jsam'))
      {
         $pth = $dfn;
         $dfn = jsam::parse(jsam::frisk($pth), $vrs);

         if (typeOf($dfn) !== obj)
         { throw new Exception('object expected from: "'.$pth.'"'); }
      }
   // -----------------------------------------------------------------------------------


   // validate
   // -----------------------------------------------------------------------------------
      if (typeOf($dfn) !== obj)
      { throw new Exception('invalid jsam definition, object expected'); }

      reset($dfn);

      $tpe = key($dfn);
      $pts = explode('+', $tpe);
      $fnc = $pts[0];
      $sub = (isset($pts[1]) ? $pts[1] : null);
      $pth = 'build.'.str_replace('/', '.', $fnc);
   // -----------------------------------------------------------------------------------


   // response :: head+body, compiled jsam
   // -----------------------------------------------------------------------------------
      $rsl = jsam::{$pth}($dfn->$tpe, $vrs, $sub);
      $rsp = new obj(['head'=>['HTTP/1.0 200 OK'], 'body'=>$rsl]);

      $rsp->head[] = 'Content-Type: '.$tpe;
      $rsp->head[] = 'Content-Length: '.strlen($rsl);

      return $rsp;
   // -----------------------------------------------------------------------------------
   });
// --------------------------------------------------------------------------------------

?>
