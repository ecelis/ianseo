var Block=1;
var SubBlock=1;
function GetContent(hide) {
	// starts getting all the content of the page
	$('#body').hide();
	$('#content').marquee('destroy');
	if(hide) {
		Rule=0;
	} else {
		$('#body').load(DirRoot+'TV/Rot/getContent.php?Tour='+TourCode+'&block='+Block+'&subblock='+SubBlock+'&rule='+Rule, function() {
			// starts the ticker!
			var duration=$('#content').outerHeight()*10;
//		$('#content img').height($('#Rss-Rss').height()*0.75);
			var Settings = $('#Settings');
			var NewSettings={delayBeforeStart: parseInt(Settings.attr('StopTime')), duration : parseInt(Settings.attr('ScrollTime'))};
			Block=Settings.attr('NextBlock');
			SubBlock=Settings.attr('NextSubBlock');
			if($('#content').html()) {
				$('#body').fadeIn(function() {

					var content=$('#content')[0];
					var wrapHeight=content.lastChild.offsetTop+content.lastChild.offsetHeight;
					var boxHeight=$('#body').outerHeight()-content.offsetTop;
					if(wrapHeight<boxHeight) {
						NewSettings.duration=NewSettings.duration*wrapHeight/boxHeight;
					}
					$('#content')
					.bind('finished', function() {GetContent(false);})
					.marquee(NewSettings);
				});
			} else {
				$('#body').fadeIn(function() {
					setTimeout(function() {
						$('#body').fadeOut(function() {
							GetContent();
						});
					}, parseInt(Settings.attr('StopTime')));
				});
			}
		});
	}
}