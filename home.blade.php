<div class="container">
	<div class="row">
		<div class="col-md-8 col-md-offset-2">
			<div class="panel panel-default">
				<div class="panel-heading">YouTube 下載編輯器 - <span class="label label-danger" id="server_connection">尚未連線</span></div>				 
				<div class="panel-body">
				
					<div class="form-group">
						<label>請輸入 YouTube 影片網頁</label>
						<input type="text" class="form-control" name="url" id="url" value="" placeholder="請輸入網頁連結，例如  https://www.youtube.com/watch?v=HNcwS4n5D2c" />
						<div>＊請參考可下載站台：<a href="https://rg3.github.io/youtube-dl/supportedsites.html" target="_blank">https://rg3.github.io/youtube-dl/supportedsites.html</a></div>
					</div>
					<button type="button" class="btn btn-primary" id="btn_download">下載影片</button>
					
					<hr />
					
					<div class="form-group">
						<label>影片播放/編輯區</label>
						<div align="center" class="embed-responsive embed-responsive-16by9">
							<video id="player" controls class="embed-responsive-item"></video>
						</div>
					</div>
					
					<hr />
					
					<div class="form-group">
						<div>切割起始時間：<input type="text" id="start_time" value="" /><input type="button" id="getStartTime" value="取得起始時間" /></div>
						<div>切割結束時間：<input type="text" id="end_time" value="" /><input type="button" id="getEndTime" value="取得結束時間" /></div>
						<button type="button" class="btn btn-primary" id="btn_split">切割影片</button>
						<button type="button" class="btn btn-primary" id="btn_snapshot">擷取圖片（png）</button>
						<button type="button" class="btn btn-primary" id="btn_mp3">擷取 MP3</button>
					</div>

					<hr />
					
					<div class="form-group">
						<div class="panel panel-primary">
							<div class="panel-heading">伺服器回傳訊息</div>
							<div class="panel-body pre-scrollable" id="msg_board"></div>
						</div>
					</div>
					
					<hr />
					
					<!-- 檔案列表 -->
					<div id="s3_list">
						<label>檔案列表</label>
					@if( isset($arr_list) && count($arr_list) > 0 )
					 	@for($i = 0; $i < count($arr_list); $i++)
					 		<?php 
					 			$arr = explode('/', $arr_list[$i]); 
					 			$value = $arr[count($arr)-1];
					 			$arr_ext = explode('.', $value);
					 			$ext = $arr_ext[count($arr_ext)-1];
					 		?>
					 		<p>
					 			<input type="button" class="deleteFile" value="刪除" data-path="{{ $value }}" />
					 			<input type="button" class="editFile" value="編輯" data-path="{{ $value }}" />
					 			@if( $ext == 'mp4' )
					 				<input type="button" class="stream" value="測試" data-path="{{ $value }}" />
					 			@endif
					 			<a class="preview" href="{{ asset('storage/'.$value) }}" target="_blank" title="{{ $value }}">{{ $value }}</a>
					 		</p>
					 	@endfor
			 		@else
			 			<p>尚無影片</p>
			 		@endif
					</div>

				 </div>
				 
			</div>
		</div>
	</div>
</div>


<!-- socket.io CDN -->
<script src="https://cdn.socket.io/socket.io-1.4.5.js"></script>

<!-- 自訂 js -->
<script>
var video_name = ''; //給切割命名用，在編輯鈕按下的那一刻來決定

$(document).ready(function(){
	//透過 web server 的 3000 port 來進行連線
    socket = io.connect('http://web_server_ip:3000/');

    //建立連結
    socket.on('connect', function() { 
        $('#server_connection').removeClass("label-danger").addClass('label-success').text('已連線');
        $('div#msg_board').append('<div>您已與伺服器連線（Node.js Socket.io）</div>');
    });

    //連線錯誤（可能 server 端有狀況）
    socket.on('connect_error',function(){
    	$('#server_connection').removeClass("label-success").addClass('label-danger').text('尚未連線');
        $('div#msg_board').append('<div>您已與伺服器斷線...........</div>');
    });

    //傳送 url 連結到伺服器
    $(document).on('click', '#btn_download', function() {
        if( $('#url').val() == "" )
        {
            alert('請輸入網址，謝謝');
            return false;
        }

    	socket.emit('url', $('#url').val());
    	$('#url').val('');
    	$('#getStartTime, #getEndTime, #btn_split, #btn_download, #btn_snapshot').attr('disabled', true);
    });

    //接收從 node.js 程式回傳的結果
    socket.on('server_msg', function(data) {
        //var data = new TextDecoder("utf-8").decode(data);
        //alert( data );
        $('div#msg_board').append('<div>' + data + '</div>');

        //div 滾輪往下滾
        $('div#msg_board').scrollTop( $('div#msg_board')[0].scrollHeight );
    });

    //當指令執行完畢後，會丟一個確認訊息 (exited code) 過來
    socket.on('server_cmd_finished', function(code) {
        switch( parseInt(code) )
        {
        	case 0:
        		getS3Files();
            break;

        	case 1:
            	alert('Node.js 程式執行出錯與終止，請至伺服器查看');
            break;
        }
        $('#getStartTime, #getEndTime, #btn_split, #btn_download, #btn_snapshot').attr('disabled', false);
    });

    //取得 video player
    var player = document.getElementById('player');

    //播放軸的目前選擇時間
    player.addEventListener("seeked", function(){
    	$('#current_time').val( toHHMMSS(player.currentTime) );
    });
    
    //編輯按鈕，開啟編輯模式 (會在播放器裡面加入 source 元素)
    $(document).on('click', '.editFile', function(){
        
    	var vp = $('video#player');
    	vp.find('source').remove();
    	video_name = $(this).attr('data-path');
    	var path = $(this).attr('data-path');
        var ext = path;
        ext = ext.split('.');
        ext = ext[ext.length-1];

        //加入 source 元素
        vp.append('<source src="/storage/' + path + '" type="video/' + ext + '">');

        //重新讀取 video 的 source 元素
        player.load();
    });

    //取得切割開始時間
    $(document).on('click', '#getStartTime', function(){
    	$('#start_time').val( toHHMMSS(player.currentTime) );
    });

  	//取得切割結束時間
    $(document).on('click', '#getEndTime', function(){
    	$('#end_time').val( toHHMMSS(player.currentTime) );
    });

  	//切割影片
    $(document).on('click', '#btn_split', function(){
    	//確認欲處理的時間是否有正確配置
    	if( checkTime() != true ) return false;
        
        if ( $('#start_time').val() == "" || $('#end_time').val() == "" )
        {
            alert('請先確實選擇欲切割之起始與結束時間，謝謝。');
            return false;
        }
        
        var obj = {};
        obj['start_time'] = $('#start_time').val();
        obj['end_time'] = $('#end_time').val();
        obj['file_path'] = $('video#player > source').attr('src');
        obj['video_name'] = video_name;
    	socket.emit('split_video', obj);

    	$('#getStartTime, #getEndTime, #btn_split, #btn_download, #btn_snapshot').attr('disabled', true);
    });
    
    //擷圖
    $(document).on('click', '#btn_snapshot', function(){ 
    	//確認欲處理的時間是否有正確配置
    	if( checkTime() != true ) return false;
    	       
        var obj = {};
        obj['current_time'] = player.currentTime;
        obj['file_path'] = $('video#player > source').attr('src');
        obj['video_name'] = video_name;
    	socket.emit('snapshot', obj);

    	$('#getStartTime, #getEndTime, #btn_split, #btn_download, #btn_snapshot').attr('disabled', true);
    });

    //轉成 mp3
    $(document).on('click', '#btn_mp3', function(){
    	//確認欲處理的時間是否有正確配置
    	if( checkTime() != true ) return false;
    	  
        var obj = {};
        obj['start_time'] = $('#start_time').val();
        obj['end_time'] = $('#end_time').val();
        obj['file_path'] = $('video#player > source').attr('src');
        obj['video_name'] = video_name;
    	socket.emit('mp3', obj);

    	$('#getStartTime, #getEndTime, #btn_split, #btn_download, #btn_snapshot').attr('disabled', true);
    });
    
	//刪除檔案
	$(document).on('click', 'input.deleteFile', function(){
		var btn = $(this);
		var path = btn.attr('data-path');
		if( confirm('確定要刪除檔案?') )
		{
			$.ajax({
				method: 'POST',
				url: '/admin/youtube/deleteFile',
				data: {
					path: path
				},
				dataType: 'html',
				timeout:{},
				statusCode: {
					404: function(){ alert('找不到頁面'); },
					500: function(){ alert('內部伺服器錯誤'); }
				},
				beforeSend: function(){},
				headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
			}).done(function( html ){
				//alert( html );
				if( parseInt(html) >= 1 )
				{
					//alert('刪除檔案成功');
					//location.reload();
					
					//刪除按鈕元素即連結元素
					btn.parent('p').remove();
				}
				else
				{
					alert('刪除失敗…');
				}
			}).fail(function(e){
				alert('傳遞失敗。請稍候再試，或是與程式設計人員聯絡，謝謝。' + '\n\n' + e.responseText);
			}).always(function(){});
		}
	});

});


//確認欲處理的時間是否有正確配置
function checkTime()
{
	var bool = true;
	var start_time = $('#start_time').val();
    var end_time = $('#end_time').val();
    if( start_time >= end_time )
    {
        alert('開始時間不能大於等於結束時間喔!!');
        bool = false;
    }
    return bool;
}

//秒數格式化成 HH:MM:SS
function toHHMMSS(sec_num){
	var sec_num = parseFloat(sec_num, 10); // don't forget the second param
    var hours   = Math.floor(sec_num / 3600);
    var minutes = Math.floor((sec_num - (hours * 3600)) / 60);
    var seconds = sec_num - (hours * 3600) - (minutes * 60);

    if (hours   < 10) {hours   = "0"+hours;}
    if (minutes < 10) {minutes = "0"+minutes;}
    if (seconds < 10) {seconds = "0"+seconds;}
    return hours+':'+minutes+':'+seconds;
}
</script>