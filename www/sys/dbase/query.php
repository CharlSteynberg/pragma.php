<?

   set::{"dbase.query"}
   (
      function($dfn)
      {
      // validate
      // --------------------------------------------------------------------------------
         $cfg = dbase::get('conf');
         $src = (isset($dfn->basis) ? $dfn->basis : $cfg->sources->auto);

         if (!isset($cfg->sources->{$src}))
         { throw new Exception('dbase source: "'.$src.'" is undefined'); }

         if (!isset($dfn->limit))
         { $dfn->limit = $cfg->noLimit; }

         $use = $cfg->sources->{$src};

         mysqli_report(MYSQLI_REPORT_STRICT);

         try
         { $dbl = new mysqli($use->host, $use->user, $use->pass, $use->base); }
         catch (Exception $err)
         { throw new Exception('dbase source: "'.$src.'" connection failed; '.$err->getMessage()); }

         $sql = '';
         $tal = new obj();
         $cal = new obj();
         $qry = new obj();
      // --------------------------------------------------------------------------------



      // fetch
      // --------------------------------------------------------------------------------
         if (isset($dfn->fetch))
         {
         // locals
         // -----------------------------------------------------------------------------
            $ftc = $dfn->fetch;
            $tpe = typeOf($ftc);
            $tmp = new obj();
         // -----------------------------------------------------------------------------


         // validate
         // -----------------------------------------------------------------------------
            if ($tpe !== obj)
            { throw new Exception('fetch: object expected'); }

            foreach ($ftc as $tbl => $col)
            {
               $tpe = typeOf($col);

               if (strpos($tbl, '.') !== false)
               {
                  if ($tpe !== str)
                  { throw new Exception('fetch: expecting {table.column:"alias"}'); }

                  $pts = explode('.', $tbl);
                  $tbl = $pts[0];

                  $col = [new obj([$pts[1]=>$col])];
               }

               if (!isset($tmp->$tbl))
               { $tmp->$tbl = new obj(); }

               foreach ($col as $i => $v)
               {
                  $tpe = typeOf($v);

                  if (is_int($i))
                  {
                     if ($tpe === str)
                     { $v = new obj([$v=>$v]); }

                     $k = key($v);
                     $v = $v->$k;
                  }
                  else
                  { $k = $i; }

                  if (isset($tal->$v))
                  { throw new Exception('fetch: "'.$v.'" is already defined; expecting unique alias'); }

                  $tmp->$tbl->$k = $v;
                  $tal->$v = $tbl;
                  $cal->$v = $k;
               }
            }

            $ftc = $tmp;
            $tmp = null;
         // -----------------------------------------------------------------------------


         // build query list
         // -----------------------------------------------------------------------------
            foreach ($ftc as $tbl => $col)
            {
               $tmp = [];

               foreach ($col as $c => $a)
               { $tmp[] = "$c AS $a"; }

               $qry->$tbl = "SELECT ".implode($tmp, ', ')." FROM $tbl";
            }
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------



      // where
      // --------------------------------------------------------------------------------
         if (isset($dfn->where))
         {
            $whr = $dfn->where;
            $flc = str::in($whr)->get('<>');
            $qsc = [chr(171), chr(187)];

            if ($flc === '[]')
            { $whr = str::in($whr)->get('><'); }

            $lst = explode(',', $whr);
            $dol = ['==', '!=', '<=', '>=', '&&', '||'];
            $sol = ['=', '!', '<', '>', '&', '|'];
            $whr = null;

            foreach ($lst as $exp)
            {
               $flc = str::in($exp)->get('<>');

               if ($flc !== '()')
               { throw new Exception('expression expected'); }

               $exp = str::in(str_replace($qsc, "'", $exp))->get('><');
               $seq = null;
               $opr = null;

               foreach ($dol as $do)
               {
                  if (strpos($exp, $do) !== false)
                  { $seq = explode($do, $exp); $opr = $do; break; }
               }

               if ($seq === null)
               {
                  foreach ($sol as $so)
                  {
                     if (strpos($exp, $so) !== false)
                     { $seq = explode($so, $exp); $opr = $do; break; }
                  }
               }

               if ($opr === null)
               { throw new Exception('unrecognized operator in expression'); }

               $var = (isset($sir->{$seq[0]}) ? $sir->{$seq[0]} : $seq[0]);

               if (!isset($tal->$var))
               { throw new Exception('where: "'.$var.'" is undefined'); }

               $lft = $cal->{$var};
               $opr = (($opr === '==') ? '=' : $opr);
               $rgt = $seq[1];
               $lke = strpos($rgt, '*');

               if ($lke !== false)
               {
                  $opr = (($opr === '!=') ? 'NOT LIKE' : 'LIKE');
                  $rgt = str_replace('*', '%', $rgt);
               }

               $tqs = $qry->{$tal->{$var}};
               $whr = "$lft $opr $rgt";
               $whr = ((strpos($tqs, 'WHERE') === false) ? ' WHERE '.$whr : ' AND '.$whr);

               $qry->{$tal->{$var}} = $tqs.$whr;
            }
         }
      // --------------------------------------------------------------------------------



      // group
      // --------------------------------------------------------------------------------
         if (isset($dfn->group))
         {
            throw new Exception('grouping is not implented, yet. Please advise.');
         }
      // --------------------------------------------------------------------------------



      // order
      // --------------------------------------------------------------------------------
         if (isset($dfn->order))
         {
            $ord = $dfn->order;
            $tpe = typeOf($ord);
            $ref = new obj(['ASC'=>'ASC', 'DSC'=>'DESC', 'RND'=>'RAND()']);
            $tmp = new obj();

            if ($tpe === str)
            {
               foreach ($ftc as $k => $v)
               { $tmp->{$k.'.id'} = $ord; }

               $ord = $tmp;
            }

            foreach ($ord as $key => $opt)
            {
               $opt = strtoupper($opt);

               if (!isset($ref->$opt))
               { throw new Exception('order options are: ASC, DSC, RND'); }

               if (strpos($key, '.') !== false)
               {
                  $pts = explode('.', $key);
                  $tbl = $pts[0];
                  $col = $pts[1];
               }
               else
               {
                  if (!isset($tal->$key))
                  { throw new Exception('order: "'.$key.'" is undefined'); }

                  $tbl = $tal->$key;
                  $col = $key;
               }

               $col .= ' ';

               if ($opt == 'RND')
               { $col = ''; }

               $qry->$tbl .= ' ORDER BY '.$col.$ref->$opt;
            }
         }
      // --------------------------------------------------------------------------------



      // limit
      // --------------------------------------------------------------------------------
         if (isset($dfn->limit))
         {
            $lmt = $dfn->limit;
            $tpe = typeOf($lmt);
            $tmp = new obj();

            if ($tpe === int)
            {
               foreach ($ftc as $k => $v)
               { $tmp->$k = $lmt; }
            }
            else
            {
               foreach ($lmt as $k => $v)
               {
                  if (!isset($ftc->$k))
                  { throw new Exception('limit: table "'.$k.'" is undefined'); }

                  $tmp->$k = $v;
               }
            }

            $lmt = $tmp;

            foreach ($lmt as $tbl => $num)
            { $qry->$tbl .= ' LIMIT '.$num; }
         }
      // --------------------------------------------------------------------------------



      // run query
      // --------------------------------------------------------------------------------
         $rsl = [];

         foreach ($qry as $tbl => $sql)
         {
         // fix RAND() - speed optimize
         // -----------------------------------------------------------------------------
            if (strpos($sql, 'RAND()') !== false)
            {
               $pts = explode(' ORDER BY RAND() ', $sql);
               $sql = $pts[0].', (select id AS sid from '.$tbl.' ORDER BY RAND() '.$pts[1].' ) tmp where id = tmp.sid';
            }
         // -----------------------------------------------------------------------------


         // -----------------------------------------------------------------------------
            $num = 0;
            $rsp = $dbl->query($sql);

            while($obj = $rsp->fetch_object())
            {
               if (!isset($rsl[$num]))
               { $rsl[$num] = new obj(); }

               foreach ($obj as $k => $v)
               { $rsl[$num]->$k = $v; }

//               $rsl[$num]->$tbl = $obj;

               $num++;
            }
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------


      // return result
      // --------------------------------------------------------------------------------
         return $rsl;
      // --------------------------------------------------------------------------------
      }
   );

?>
