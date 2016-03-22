<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 */
xapp_import('xapp.Path.Utils');

/***
 * Example server plugin.
 * @remarks
    - This class is running in the CMS context already!
    - A function's result will be wrapped automatically into the specified transport envelope, eg: JSON-RPC-2.0 or JSONP
    - Implementing Xapp_Rpc_Interface_Callable is just for demonstration
    -
 */


class XShell extends Xapp_Commander_Plugin{
    /***
     * @param $command
     * @param null $cwd current working directory in this format root://myfolder.
     * @param null $options some options
     * @return string
     */
    protected function runBash($command,$cwd=null,$options = null){

	    $shell_result_final = "";
        $command = $command." 2>&1";

	    if($cwd && file_exists($cwd)){
            chdir($cwd);
        }
	    /**
	     * try to get a system shell
	     */


        $plgPath = realpath(dirname(__FILE__));
        $ansi2html = realpath( $plgPath . DIRECTORY_SEPARATOR . 'ansi2html.sh');
        if(file_exists(realpath($ansi2html))){
            if(PHP_OS =="Linux"){
                $command  = $command . ' | ' . $ansi2html;
            }
        }

        if(is_callable('system')) {

            ob_start();

            @system($command);

            $shell_result_final = ob_get_contents();
            ob_end_clean();

            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        if(is_callable('shell_exec')){
            $shell_result_final = @shell_exec($command);
            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        if(is_callable('exec')) {

            @exec($command,$shell_result);


            if(!empty($shell_result)){
                foreach($shell_result as $shell_res_item) {
                    $shell_result_final .= $shell_res_item;
                }
            }
            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        if(is_callable('passthru')) {

            ob_start();

            @passthru($command);

            $shell_result_final = ob_get_contents();

            ob_end_clean();

            if(!empty($shell_result_final)){
                return $shell_result_final;
            }
        }

        if(is_callable('proc_open')) {

            $shell_descriptor_spec = array(

                0 => array("pipe", "r"),

                1 => array("pipe", "w"),

                2 => array("pipe", "w")
            );

            $shell_proc = @proc_open($command, $shell_descriptor_spec, $shell_pipes, getcwd(), array());

            if (is_resource($shell_proc)) {

                while($shell_in_proc_open = fgets($shell_pipes[1])) {

                    if(!empty($shell_in_proc_open)){
                        $shell_result_final .= $shell_in_proc_open;
                    }
                }
                while($s_se = fgets($shell_pipes[2])) {
                    if(!empty($s_se)) $shell_result_final .= $s_se;
                }
            }
            @proc_close($shell_proc);
            if(!empty($shell_result_final)){
                return $shell_result_final;
            }
        }
        //test popen
        if(is_callable('popen')){

            $shell_open = @popen($command, 'r');


            if($shell_open){
                while(!feof($shell_open)){
                    $shell_result_final .= fread($shell_open, 2096);
                }
                pclose($shell_open);
            }
            if(!empty($shell_result_final)) {
                return $shell_result_final;
            }
        }
        return "";
    }

	/**
	 * @param $shellType {string}
	 * @param $cmdBase64 {string}
	 * @param $cwd
	 * @return string
	 */
    public function run($shellType='sh',$cmdBase64,$cwd=null){




	    //determine real fs path
	    if($this->directoryService){

		    $mount = XApp_Path_Utils::getMount($cwd);
		    $rel = XApp_Path_Utils::getRelativePart($cwd);
		    $vfs = null;
		    if($mount){
			    $vfs = $this->directoryService->getFileSystem($mount);
			    if($vfs){
			        $fullPath = $vfs->toRealPath($mount . DIRECTORY_SEPARATOR . $rel);
				    if(file_exists($fullPath)){
					    $cwd = $fullPath;
				    }
			    }
		    }

	    }

	    $code = base64_decode($cmdBase64);

        if($shellType==='sh'){
            $res = $this->runBash($code,$cwd);
            return $res;
        }elseif($shellType==='php'){

            xapp_import('xapp.commander.plugins.Shell.server.vendor.autoload');

            $sandbox = new PHPSandbox\PHPSandbox;

            //this will disable function validation

            $sandbox->set_option('validate_functions', false);
            $sandbox->set_option('validate_variables', false);
            $sandbox->set_option('validate_globals', false);
            $sandbox->set_option('validate_constants', false);
            $sandbox->set_option('validate_magic_constants', false);
            $sandbox->set_option('validate_superglobals', false);
            $sandbox->set_option('validate_aliases', false);
            $sandbox->set_option('validate_interfaces', false);


            $sandbox->set_option('validate_classes', false);
            $sandbox->set_option('validate_primitives', false);

            $sandbox->set_option('allow_functions', true);
            $sandbox->set_option('sandbox_includes', true);
            $sandbox->set_option('allow_globals', true);
            $sandbox->set_option('allow_objects', true);

            $sandbox->set_option('allow_constants', true);
            $sandbox->set_option('allow_namespaces', true);
            $sandbox->set_option('allow_aliases', true);
            $sandbox->set_option('allow_classes', true);
            $sandbox->set_option('allow_generators', true);
            $sandbox->set_option('', true);
            $sandbox->set_option('', true);
            $sandbox->set_option('', true);
            $sandbox->set_option('', true);

            $sandbox->set_option('', true);




            $res = $sandbox->execute($code);
            /*
            $sandbox->execute('<?php echo system("ping google.com"); ?>');
            */
            return $res;

        }
        return 'not implemented';
    }

    /***
     * Invoked by the plugin manager, before 'load'!. time to register our subscriptions
     * @return int|void
     */
    public function setup(){
        /***
         * Listen to file changes
         */
        /*
        xcom_subscribe(XC_OPERATION_WRITE_STR,function($mixed)
        {
            if (preg_match(XSVN::MATCH_PATTERN, $mixed[XAPP_EVENT_KEY_PATH])) {
                XSVN::instance()->onSavingSVNFile($mixed);
            }
        });
        */
    }



    /***
     * Invoked by the plugin manager, time to pull dependencies but we don't !
     * @return int|void
     */
    public function load(){}

    /**
     * Xapp_Singleton interface impl. Its actually done in the base class,...
     *
     * static singleton method to create static instance of driver with optional third parameter
     * xapp options array or object
     *
     * @error 15501
     * @param null|mixed $options expects optional xapp option array or object
     * @return XSVN
     */
    public static function instance($options = null)
    {
        if(self::$_instance === null)
        {
            self::$_instance = new self($options);
        }
        return self::$_instance;
    }

}