<?php

namespace App\Controller;
use App\Controller\Controller;
use App\Models\Inventory;
use App\Models\V_bom_bom_rpt;
use App\Models\V_bom_opcomponent_rpt;
use App\Models\V_bom_parent_rpt;
use Illuminate\Http\Request;
use WorkerF\Http\Requests;
use App\Models\Test;

class BomController extends Controller
{
    //获取单个物料清单
    public function bom_get($pinvcode,Request $request)
    {
        //$pinvcode  //母件物料编码
        //1.根据母件 pcba 物料编码查询母件信息
        $v_bom_parent_rpt=V_bom_parent_rpt::where('InvCode',$pinvcode)
            ->select(
                'BomId',
                'ParentId', //母件物料id
                'InvCode' //物料编码
            )
            ->first();  //得到母件物料信息
        if(!$v_bom_parent_rpt){
            return APIReturn(211,"母件物料{$pinvcode}不存在!");
        }

        //1.1 根据$pinvcode 查询存货档案
        $inventory=Inventory::where('cInvCode',$pinvcode)
            ->select(
                'cInvCode',  //存货编码
                'cInvName',  //存货名称
                'cInvStd',  //规格型号
                'cInvCCode'  //存货大类编码
            )
            ->first();
        if(!$inventory){
            return APIReturn(213,"存货档案{$inventory}不存在!");
        }

        //2.根据$v_bom_parent_rpt->BomId 查询bom资料信息
        $v_bom_bom_rpt=V_bom_bom_rpt::where('BomId',$v_bom_parent_rpt->BomId)->first(); //得到bom信息
        if(!$v_bom_bom_rpt){
            return APIReturn(212,"母件物料{$pinvcode}对应的bom数据不存在!");
        }

        //3.根据$v_bom_parent_rptr->BomId 查询子件物料信息
        $v_bom_opcomponent_rpt=V_bom_opcomponent_rpt::where('BomId',$v_bom_parent_rpt->BomId)
            ->select(
                'OpComponentId', //BOM子件资料标识Id
                'BomId', //
                'ComponentId', //子件物料ID
                'EffBegDate', //子件生效日
                'EffEndDate', //子件失效日
                'InvCode', //物料编码
                'BaseQtyN', //基本用量分子
                'BaseQtyD', //基本用量分目
                'FVFlag' //固定变动
            )
            ->get();  //子件 集合

        $res=['bom_bom'=>$v_bom_bom_rpt,'inventory'=>$inventory,'bom_parent'=>$v_bom_parent_rpt,'bom_children'=>$v_bom_opcomponent_rpt];

        return APIReturn(200,"success",$res);
    }

    //批量获取物料清单  该方法暂时不用
    public function batch_get(Request $request)
    {
//        return 445;
        $res=V_bom_bom_rpt::limit(5)->get();
        return APIReturn(200,"batch_get",$res);
    }

    //根据母件物料编码，查询母件物料id
    public function get_parentid_by_pinvcode($pinvcode,Request $request){
        $ParentId=V_bom_parent_rpt::where('InvCode',$pinvcode)->value('ParentId');
        if(!$ParentId){
            return APIReturn(211,"母件物料{$pinvcode}对应的物料id不存在!");
        }
        return APIReturn(200,"success",['ParentId'=>$ParentId]);
    }
}
