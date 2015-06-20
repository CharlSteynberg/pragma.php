
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
               var exp, ico, tgt, dts, cls;

               tgt = obj.parentNode.parentNode.getElementsByClassName('sectBlock')[0];
               dts = obj.parentNode.parentNode.getElementsByClassName('elipsis')[0];
               cls = obj.className;
               exp = tgt.getAttribute('exp');

               if (!exp){ tgt.set({exp:1}); exp = 1; }

               exp = ((exp < 1) ? 1 : 0);
               ico = ((exp < 1) ? 'chevron-right' : 'chevron-down');

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

               cls = cls.split('chevron-right').join(ico);
               cls = cls.split('chevron-down').join(ico);

               obj.className = cls;
            }
         // -----------------------------------------------------------------------------
         },
      // --------------------------------------------------------------------------------


      // field :: field interaction
      // --------------------------------------------------------------------------------
         field:
         {
         // tglInf :: toggle info bubble
         // -----------------------------------------------------------------------------
            tglInf:function(obj)
            {
               var bbl = obj.parentNode.parentNode.getElementsByClassName('bubl')[0];

               hide('.bubl');
               togl(bbl);
            },
         // -----------------------------------------------------------------------------

         // test :: validation
         // -----------------------------------------------------------------------------
            test:function(obj)
            {
               var inf = obj.parentNode.parentNode.parentNode.getElementsByClassName('infIco')[0];
               var exp = new RegExp(obj.pattern);
               var icn = inf.className;
               var ibg = 'none';
               var bbl = obj.parentNode.parentNode.parentNode.getElementsByClassName('bubl')[0];

               var val = obj.value;
               var tst = (exp ? exp.test(val) : true);

               if (tst == true)
               {
                  icn = icn.split('icon-info').join('icon-check');
                  icn = icn.split('icon-exclamation-circle').join('icon-check');
                  icn = icn.split('color-info').join('color-good');
                  icn = icn.split('color-need').join('color-good');
                  icn = icn.split('color-fail').join('color-good');

                  ibg = 'hsla(120,60%,92%,1)';
               }
               else
               {
                  icn = icn.split('icon-info').join('icon-exclamation-circle');
                  icn = icn.split('icon-check').join('icon-exclamation-circle');
                  icn = icn.split('color-info').join('color-fail');
                  icn = icn.split('color-need').join('color-fail');
                  icn = icn.split('color-good').join('color-fail');

                  ibg = 'hsla(0,75%,90%,1)';

                  show(bbl);
               }

               inf.className = icn;
               inf.parentNode.style.background = ibg;
            },
         // -----------------------------------------------------------------------------
         }
      // --------------------------------------------------------------------------------
      }
   });
