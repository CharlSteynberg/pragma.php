<?

   Jsam::set('parse.for', function($dfn, $vrs)
   {
      $opl = explode(' ', '!= == += -= *= /= <= >= ++ -- = + - * / < >');
      $exp = explode(';', $dfn);
      $evl = '';
      $rsl = [];
      $avl = new obj();

      foreach ($vrs as $k => $v)
      { $avl->$k = $v; }

      foreach($exp as $arg)
      {
         $sub = explode(',', $arg);

         foreach($sub as $sum)
         {
            foreach($opl as $opr)
            {
               if (strpos($sum, $opr) !== false)
               {
                  $pts = explode($opr, $sum);

                  for ($p=0; $p<2; $p++)
                  {
                     if ((strlen($pts[$p]) > 0) && ctype_alpha($pts[$p][0]))
                     {
                        $pts[$p] = str_replace('.', '->', $pts[$p]);
                        $pts[$p] = '$avl->'.$pts[$p];
                     }
                  }

                  $evl .= $pts[0].$opr.$pts[1];
                  break;
               }
            }

            $evl .= ',';
         }

         $evl .= ';';
      }

      $exp = substr(str_replace(',;', ';', $evl), 0, -1);
      $str = '
               for('.$exp.')
               {
                  $row = new obj();

                  foreach ($avl as $avk => $avv)
                  {
                     if (!property_exists($vrs, $avk))
                     { $row->$avk = $avv; }
                  }

                  $rsl[] = $row;
               }
             ';

      eval($str);

      return $rsl;
   });

?>
