# Video_Downloader_Splitter_Converter
Youtube Video Downloader, Splitter and Converter (ubuntu, nodejs, socketio, ffmpeg) 

# Prerequisite 
- youtube-dl:
  - You need to download [youtube-dl](https://rg3.github.io/youtube-dl/) and modify it with executable access.
  - Supported sites of youtube-dl: [link](https://rg3.github.io/youtube-dl/supportedsites.html)
  - Please refer to youtube-dl README: [youtube-dl README](https://github.com/rg3/youtube-dl/blob/master/README.md#readme)
- Other: 
  - I built this project in Laravel framework, thus, you might as well alter these source code and put them into your project environment.
  - You need to install [FFmpeg](https://ffmpeg.org/), meanwhile, download [Node.js](https://nodejs.org/), [Socket.io](http://socket.io/) and [Express](https://www.npmjs.com/package/express).

# File introduction
**home.blade.php:**
>View of Laravel, you can put it into your VIEW folder.

**web.php:**
>Router of Laravel, you can use it to route for your URL.

**YoutubeController.php:**
>Controller of Laravel, you can put it into your CONTROLLER folder.

**YouTubeDownloadListener.js:**
>Node.js command listener (important)

# Demo
[Youtube Video Downloader, Splitter and Converter](https://www.youtube.com/watch?v=2whO3-DBXkw)

# Note
This project helps us to download videos, split videos, make a snapshot and convert to mp3.

# My LinkedIn
[link](https://tw.linkedin.com/in/telunyang)
