
   global.add
   ({
      bits:
      {
      // sect :: section interaction
      // --------------------------------------------------------------------------------
         sect:
         {
         // tglExp :: toggle expand & contract
         // -----------------------------------------------------------------------------
            tglExp:function(obj)
            {
               var exp, ico, tgt, dts, pts;

               tgt = obj.parentNode.parentNode.getElementsByClassName('sectBlock')[0];
               dts = obj.parentNode.parentNode.getElementsByClassName('elipsis')[0];
               exp = tgt.getAttribute('exp');

               if (!exp){ tgt.set({exp:1}); exp = 1; }

               exp = ((exp < 1) ? 1 : 0);
               ico = ((exp < 1) ? 'arrow-right-17' : 'arrow-down-16');

               tgt.set({exp:exp});

               if (exp < 1)
               {
                  tgt.style.display = 'none';
                  dts.style.display = 'inline';
               }
               else
               {
                  tgt.style.display = 'block';
                  dts.style.display = 'none';
               }

               pts = obj.className.split(' ');
               pts[0] = 'icon-'+ico;

               obj.className = pts.join(' ');
            }
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------
      }
   });
