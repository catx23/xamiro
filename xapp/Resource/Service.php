<?php
/**
 * @version 0.1.0
 *
 * @author https://github.com/mc007
 * @license : GPL v2. http://www.gnu.org/licenses/gpl-2.0.html
 * @package XApp\Resource
 */

xapp_import('xapp.Path.Utils');
xapp_import('xapp.Service.Service');

class XApp_Resource_Service extends XApp_Service
{

    public function createResource($resource,$test=false){
        $res = $this->getObject()->createResource($resource);
        if(count($res)){
            return $this->toRPCError(1,$res);
        }
        return $res;
    }

	public function updateResource($resource,$test=false){
		$res = $this->getObject()->updateResource($resource);
		if(count($res)){
			return $this->toRPCError(1,$res);
		}
		return $res;
	}
	public function removeResource($resource,$test=false){
		$res = $this->getObject()->removeResource($resource);
		if(count($res)){
			return $this->toRPCError(1,$res);
		}
		return $res;
	}


	public function ls(){
	    $error = array();
        $res = $this->getObject()->ls($error);
        if(count($error)){
            return $this->toRPCError(1,$error);
        }
        return $res;
    }
}