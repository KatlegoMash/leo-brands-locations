
$(function() {
var store = 120;
var countbrands = 0;
var totalCampaigns = 0;
var countPublishers = 0;
var countClients = 0;
var counter = 0;
var getCampaign = [];
var getStartDates = [];
var getEndDates = [];
var lengthTotal = 0;
var impressionSum = 0;
var clickSum = 0;
var barTable;
var getName=[];
var dt;
var Name_array = [];
var I_array = [];
var C_array = [];
//    Morris.Area({
//        element: 'morris-area-chart',
//        data: [{
//            period: '2010 Q1',
//            iphone: 2666,
//            ipad: null,
//            samsung:400,
//            itouch: 2647
//        }, {
//            period: '2010 Q2',
//            iphone: 2778,
//            ipad: 2294,
//            samsung:4200,
//            itouch: 2441
//        }, {
//            period: '2010 Q3',
//            iphone: 4912,
//            ipad: 1969,
//            samsung:400,
//            itouch: 2501
//        }, {
//            period: '2010 Q4',
//            iphone: 3767,
//            ipad: 3597,
//            samsung:1200,
//            itouch: 5689
//        }, {
//            period: '2011 Q1',
//            iphone: 6810,
//            ipad: 1914,
//            samsung:9400,
//            itouch: 2293
//        }, {
//            period: '2011 Q2',
//            iphone: 5670,
//            ipad: 4293,
//            samsung:3500,
//            itouch: 1881
//        }, {
//            period: '2011 Q3',
//            iphone: 4820,
//            ipad: 3795,
//            samsung:4800,
//            itouch: 1588
//        }, {
//            period: '2011 Q4',
//            iphone: 15073,
//            ipad: 5967,
//            samsung:4001,
//            itouch: 5175
//        }, {
//            period: '2012 Q1',
//            iphone: 10687,
//            ipad: 4460,
//            itouch: 2028
//        }, {
//            period: '2012 Q2',
//            iphone: 8432,
//            ipad: 5713,
//            samsung:4050,
//            itouch: 1791
//        }],
//        xkey: 'period',
//        ykeys: ['iphone', 'ipad', 'itouch','samsung'],
//        labels: ['iPhone', 'iPad', 'iPod Touch','samsung g'],
//        pointSize: 2,
//        hideHover: 'auto',
//        resize: true
//    });
    $.ajax({
    	dataType: "json",
    	url: "campaigns/json/active"
    	}).done(function(resultThree){
    		$(resultThree).each(function(index2,value2){   			
    			var getResultDate2 = value2.data;
    			
    			$.each(getResultDate2, function(index,value){ 
    				getStartDates[counter] = value.startDate;
    				getEndDates[counter] = value.endDate;
    				getCampaign[counter] = value.campaignId;
    				getName[counter] = value.campaignName;
    				counter++;
						 									    	 			                    	
			});
 
    			lengthTotal = getCampaign.length;
    			for (var i=0 ;i<lengthTotal;i++){  
    				(function (i){
    					impressionSum = 0;
    					clickSum = 0;
    			$.ajax({
    		    	dataType: "json",
					url: "reporting/dailystats/"+getCampaign[i]+"/"+getStartDates[i]+"/"+getEndDates[i]
    		    	   }).done(function(resultThree){
        		    
        		    	     		$(resultThree).each(function(index2,value2){ 
        		    	     			impressionSum = eval(impressionSum) + eval(value2.impressions);
        		    	     			clickSum = eval(clickSum) + eval(value2.clicks);
								        
								    
									//valueInside++; 									    	 			                    	
							    });// End resultthree

    			Name_array[i] = getName[i];
    			I_array[i]=impressionSum;
    			//I_array = [98, 50, 75, 50, 75, 100];
        		//C_array = [48, 40, 46, 40, 65, 90];
        		//Name_array = ['b', 'd', 'e', 'f', 'g', 'h'];
    			C_array[i] = clickSum; 
//    			if (I_array[i]=="undefined"){I_array[i] = 0;}
//    			if (C_array[i]=="undefined"){C_array[i] = 0;}

//        		var s = "";
//                 
//        		for (var j = 1; j < I_array.length; j++) {
//        		    if (j > 1) s += ",";
//        		    s += "{ \"y\":\"" + Name_array[1] + "\", \"a\":" + I_array[1] + ", \"b\": " + C_array[1] + " }";
//        		}
        			
//        		var text = "{\"data\": [";
//        		text += s;
//        		text += "]}";
//        		var obj = JSON.parse(text);
//        		 dt = obj.data;	
//        		.log(dt);

    		    });
        		  //Bar chart
        		
        	
    				})(i);
    		}
    		    Morris.Bar({
        	        element: 'morris-bar-chart',
        	        data:
        	        	dt
        	        	,
        	        xkey: 'y',
        	        ykeys: ['a', 'b'],
        	        labels: ['Impressions', 'Clicks'],
        	        hideHover: 'auto',
        	        resize: true
        	    });
    			totalCampaigns = getResultDate2.length; 			
    		});
    		
    		lengthTotal = getCampaign.length;
    		
    		$.ajax({
    	    	dataType: "json",
    	    	url: "brands/json"
    	    	}).done(function(resultTwo){
    	    		$(resultTwo).each(function(index,value){   			
    	    			var getResultDate = value.data;
    	    			countbrands = getResultDate.length;   			
    	    });
    	    		
    		$.ajax({
    	    	dataType: "json",
    	    	url: "publishers/json"
    	    	}).done(function(resultthree){
    	    		$(resultthree).each(function(index3,value3){   			
    	    			var getResultDate3 = value3.data;
    	    			countPublishers = getResultDate3.length;   			
    	    });
    	    		
    		$.ajax({
    	    	dataType: "json",
    	    	url: "clients/json"
    	    	}).done(function(resultthree){
    	    		$(resultthree).each(function(index3,value3){   			
    	    			var getResultDate3 = value3.data;
    	    			countClients = getResultDate3.length;   			
    	    });
    		
    		Morris.Donut({
		        element: 'morris-donut-chart',
		        data: [{
		            label: "Available Publishers",
		            value: countPublishers
		        }, {
		            label: "Active Campaigns",
		            value: totalCampaigns
		        }, {
		            label: "Total Brands",
		            value: countbrands
		        }, {
		            label: "Available Clients",
		            value: countClients
		        }],
		        resize: true
    		});

		    }); 
    	  });//#clients
    	});//#brand
    });
    //
    	 		

});
