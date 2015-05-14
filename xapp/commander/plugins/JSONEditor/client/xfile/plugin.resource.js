var profile = (function(){
	var testResourceRe = /^xapp\/tests\//,

		copyOnly = function(filename, mid){
			var list = {
                "xapp/xapp.profile":1,
				"xapp/package.json":1
			};
			return (mid in list) || /^xapp\/_base\/config\w+$/.test(mid) || (/^xapp\/resources\//.test(mid) && !/\.css$/.test(filename)) || (/^cssMobile\//.test(mid) && !/\.css$/.test(filename)) || /(png|jpg|jpeg|gif|tiff)$/.test(filename);
		};

	return {
		resourceTags:{
			test: function(filename, mid){
				return testResourceRe.test(mid) || mid=="xapp/tests" || mid=="xapp/robot" || mid=="xapp/robotx";
			},

			copyOnly: function(filename, mid){
				return copyOnly(filename, mid);
			},

			amd: function(filename, mid){
				return !testResourceRe.test(mid) && !copyOnly(filename, mid) && /\.js$/.test(filename);
			}
		},

		trees:[
			[".", ".", /(\/\.)|(~$)/]
		]
	};
})();
