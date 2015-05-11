
   keyboard.bind(192, function()
   {
      var hud = get('devHud');

      if (hud.status == 'off')
      {
         hud.style.display = 'block';
         hud.set({status:'on'});
      }
      else
      {
         hud.style.display = 'none';
         hud.set({status:'off'});
      }
   });
