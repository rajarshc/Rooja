function processSlideshow(elem, imageList, imageDuration, fadeSpeed, current) 
{
	var listSize = imageList.length;
    if (!current || current >= listSize) current = 0;
    if (!imageDuration) imageDuration = 2000;
    if (!fadeSpeed) fadeSpeed = 1000;
    $(elem + " img").attr("src", imageList[current]);
    if (current == (listSize - 1)) { $(elem).css("background", "transparent url(" + imageList[0] + ") no-repeat");
    } else {
        $(elem).css("background", "transparent url(" + imageList[current + 1] + ") no-repeat");
    }
    $(elem + " img").animate({ opacity: "1" }, imageDuration).
	      animate({ opacity: "0.01" }, fadeSpeed, function() 
		   { 
		     $(this).css("opacity", "1"); processSlideshow(elem, imageList, imageDuration, fadeSpeed, current + 1) 
			});

} 





	
	