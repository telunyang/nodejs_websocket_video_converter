var app = require('/home/test/node/lib/node_modules/express')();
var server = require('http').Server(app);
var io = require('/home/test/node/lib/node_modules/socket.io')(server);
var port = 3000;
var spawn = require('child_process').spawn;

app.get('/youtube', function(req, res){
	res.sendFile(__dirname + '/index.html');
});

server.listen(port, function(){
	console.log('listening on *:' + port);
});

io.on('connection', function(socket){
	console.log('a user connected!!');
	
	//用戶端斷線時事件
	socket.on('disconnect', function(){
		console.log('a user disconnected....');
	});
	
	//接收用戶端的 url 連結
	socket.on('url', function(url){
		//socket.emit('server_msg', '[' + getTodayDate() + '] ' + data);
		//%(title)s_%(id)s.%(ext)s
		var ls = spawn("youtube-dl", 
				[
					"-o", 
					"/home/test/TestProjects/test/public/storage/%(id)s.%(ext)s", 
					"-f", 
					"bestvideo[ext=mp4]+bestaudio[ext=m4a]/best[ext=mp4]/best", 
					url,
				]);
		
		//正常輸出
		ls.stdout.on('data', (data) => {
			console.log(`stdout: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stdout => ' + data);
		});
		
		//錯誤訊息輸出
		ls.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stderr => ' + data);
		});
		
		//指令執行輸出
		ls.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
			
			//回傳執行結束代碼
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'child process exited code => ' + code);
			
			//傳一個執行結束的代碼，到 web page 的 jquery 事件上，以便存取 Files
			socket.emit('server_cmd_finished', code);
		});
	});
	
	//切割影片
	socket.on('split_video', function(obj){
		//socket.emit('server_msg', '[' + getTodayDate() + '] ' + data);
		//%(title)s_%(id)s.%(ext)s
		var ls = spawn("time", 
				[
					"ffmpeg", 
					"-i", 
					"/home/test/TestProjects/test/public" + obj['file_path'], 
					"-ss",
					obj['start_time'],
					"-to",
					obj['end_time'], 
					"-vcodec", 
					"libx264", 
					"-acodec", 
					"aac", 
					"-strict",
					"-2", 
					"-f", 
					"mp4",
					"-y",
					"/home/test/TestProjects/test/public/storage/split_" + obj['video_name']
				]);
		
		//正常輸出
		ls.stdout.on('data', (data) => {
			console.log(`stdout: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stdout => ' + data);
		});
		
		//錯誤訊息輸出
		ls.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stderr => ' + data);
		});
		
		//指令執行輸出
		ls.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
			
			//回傳執行結束代碼
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'child process exited code => ' + code);
			
			//傳一個執行結束的代碼，到 web page 的 jquery 事件上，以便存取 Files
			socket.emit('server_cmd_finished', code);
		});
	});
	
	
	//擷取當前時間的快照圖片
	socket.on('snapshot', function(obj){
		//取得影片主檔名名稱 (這方式不好，以後會再作修正)
		var video_name = obj['video_name'];
		video_name = video_name.split('.');
		video_name = video_name[0];
        
		var ls = spawn("time", 
				[
					"ffmpeg", 
					"-i", 
					"/home/test/TestProjects/test/public" + obj['file_path'], 
					"-ss",
					obj['current_time'],
					"-vframes",
					"1",
					"/home/test/TestProjects/test/public/storage/snapshot_" + video_name + ".png"
				]);
		
		//正常輸出
		ls.stdout.on('data', (data) => {
			console.log(`stdout: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stdout => ' + data);
		});
		
		//錯誤訊息輸出
		ls.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stderr => ' + data);
		});
		
		//指令執行輸出
		ls.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
			
			//回傳執行結束代碼
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'child process exited code => ' + code);
			
			//傳一個執行結束的代碼，到 web page 的 jquery 事件上，以便存取 Files
			socket.emit('server_cmd_finished', code);
		});
	});
	
	//擷取當前時間的快照圖片
	socket.on('mp3', function(obj){
		//取得影片主檔名名稱 (這方式不好，以後會再作修正)
		var video_name = obj['video_name'];
		video_name = video_name.split('.');
		video_name = video_name[0];
        
		var ls = spawn("time", 
				[
					"ffmpeg", 
					"-i", 
					"/home/test/TestProjects/test/public" + obj['file_path'], 
					"-ss",
					obj['start_time'],
					"-to",
					obj['end_time'], 
					"-b:a",
					"192K",
					"-vn",
					"/home/test/TestProjects/test/public/storage/music_" + video_name + ".mp3"
				]);
		
		//正常輸出
		ls.stdout.on('data', (data) => {
			console.log(`stdout: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stdout => ' + data);
		});
		
		//錯誤訊息輸出
		ls.stderr.on('data', (data) => {
			console.log(`stderr: ${data}`);
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'stderr => ' + data);
		});
		
		//指令執行輸出
		ls.on('close', (code) => {
			console.log(`child process exited with code ${code}`);
			
			//回傳執行結束代碼
			socket.emit('server_msg', '[' + getTodayDate() + '] ' + 'child process exited code => ' + code);
			
			//傳一個執行結束的代碼，到 web page 的 jquery 事件上，以便存取 Files
			socket.emit('server_cmd_finished', code);
		});
	});
});

//取得今天的日期，讓使用者送出訊息時參考用
function getTodayDate() {
    var str = '';

    // 宣告日期物件
    var today = new Date();

    // 年
    var today_year = today.getFullYear();
    str += today_year;

    // 月
    var today_month = today.getMonth() + 1;
    if (today_month >= 10)
        str += '-' + today_month;
    else
        str += '-0' + today_month;

    // 日
    var today_date = today.getDate();
    if (today_date >= 10)
        str += '-' + today_date;
    else
        str += '-0' + today_date;

    var today_hour = today.getHours();
    if (today_hour >= 10)
        str += ' ' + today_hour;
    else
        str += ' 0' + today_hour;

    var today_minute = today.getMinutes();
    if (today_minute >= 10)
        str += ':' + today_minute;
    else
        str += ':0' + today_minute;

    var today_second = today.getSeconds();
    if (today_second >= 10)
        str += ':' + today_second;
    else
        str += ':0' + today_second;
    return str;
}