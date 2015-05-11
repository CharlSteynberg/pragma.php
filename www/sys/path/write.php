<?

   set::{"path.write"}
   (
      function($pth, $dfn)
      {
         $dfn = to::str($dfn);
         $flh = fopen($pth, 'w');
         $flw = fwrite($flh, $dfn);
         $flc = fclose($flh);

         return true;
      }
   );

?>
