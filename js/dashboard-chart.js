function optimizationChecking(call_back) {
    var ret;
    jQuery.ajax({
        url: myAjax.ajax_url,
        async: false,
        type: 'post',
        data: {'action': 'opt_checking'},
        dataType: 'json',
        beforeSend: function() {
        },
        success: function(response) {
            console.log(response);
            ret = response;
            for (sid in response) {
                jQuery('#' + sid).removeClass('hz-loading').text(response[sid][0] + '/' + response[sid][1]);
            }
            console.log(typeof (call_back));
            if (typeof (call_back) == "function") {
                call_back(response);
            }
        },
        error: function() {

        }

    });

    return ret;
}


function drawChart(metacontent,notmetacontent, imagemeta,notimagemeta, imageoptimi,notimageoptimi) {

    var doughnutData = [
        {
            value: metacontent,
            color: "rgba(92,155,213,1)",
            highlight: "#5c9bd5",
            label: "Content Meta- Completed"
        },
        {
            value: notmetacontent,            
            color: "#CECECE",            
            label: "Content Meta- Imcompleted"
        },
        {
            value: imagemeta,
            color: "rgba(241,145,79,1)",
            highlight: "#ed7d31",
            label: "Image Meta - Completed"
        },
        {
            value: notimagemeta,
            color: "#CECECE",              
            label: "Image Meta - Imcompleted"
        },
        
        {
            value: imageoptimi,
            color: "rgba(70,191,189,1)",
            highlight: "#46BFBD",
            label: "Image Optimized"
        },
        {
            value: notimageoptimi,
             color: "#CECECE",                    
            label: "Image not Optimized"
        }


    ];

    console.log(doughnutData);
        var ctx = document.getElementById("chart-area").getContext("2d");
        window.myDoughnut = new Chart(ctx).Doughnut(doughnutData, {
            responsive: true,
            tooltipTitleFontSize: 13,
            tooltipXOffset: 5,
            tooltipTemplate: "<%if (label){%><%=label%>: <%}%><%= ((value*3) %1 !=0)?(value*3).toFixed(2):(value*3) %> %",
            percentageInnerCutout: 70,
            scaleShowLabels: true,
            segmentShowStroke  : false,

        });
    

}
