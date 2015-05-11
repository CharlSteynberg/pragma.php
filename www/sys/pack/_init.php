<?

   set::{'pack.js'}
   (
      function ($d, $l=null)
      {
         require_once('sys/pack/packer.php');

         $p = null;

         if (str::typeOf($d) === pth)
         {
            $p = $d;
            $d = file_get_contents($p);
            $p = str_replace('/', '.', $p);
         }

         $l = (($l === null) ? 62 : $l);
         $z = new Packer($d, $l, true, false);
         $r = $z->pack();

         if ($p !== null)
         { path::write('sys/pack/cache/'.$p, $r); }

         return $r;
      }
   );

?>
