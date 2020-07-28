window.execOnMounted.push(function() {
   if ($('.main-page__intro').length > 0) {
       const videos = ['screen1.mp4', 'screen2.mp4', 'screen3.mp4', 'screen4.mp4', 'screen5.mp4', 'screen6.mp4', 'screen7.mp4'];
       const noise = `/splashscreen/videos/noise.mp4`;
       const loadVideo = (previous) => {
           if ($('.main-page__intro').length === 0) {
                return;
           }
           let videosToSelectFrom = JSON.parse(JSON.stringify(videos));
           if (previous) {
               videosToSelectFrom = videosToSelectFrom.filter(video => video !== previous);
           }
           let selected = videosToSelectFrom[Math.floor(Math.random() * videosToSelectFrom.length)];
           let video = `/splashscreen/videos/${selected}`;
           let videoEl = $('.main-page__intro__video')[0];
           videoEl.setAttribute('loop', true);

           let request = new XMLHttpRequest();
           request.onload = function() {
               videoEl.setAttribute('src', video);
               videoEl.removeAttribute('loop');
               videoEl.load();
               videoEl.onended = function() {
                   videoEl.setAttribute('src', noise);
                   videoEl.play();
                   setTimeout(() => {
                       loadVideo(selected);
                   }, 500);
               }
           };
           request.open("GET", video);
           request.responseType = "blob";
           request.send();
       };
       loadVideo();
   }
});
