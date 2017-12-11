<?php

namespace Bantenprov\DashboardEpormas\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;
use Bantenprov\DashboardEpormas\Models\EpormasCounter;
use Bantenprov\DashboardEpormas\Models\EpormasCategory;
use Bantenprov\DashboardEpormas\Models\EpormasCity;
use Validator, Image, Session, File, Response, Redirect, Exception;

class DashboardEpormasController extends Controller
{
    public function epormas()
    {
      //Pegawai Epormas
      $grafikepormascounter = DB::table('epormas_counter')
                    ->select('tahun','bulan','city_id', DB::raw('SUM(count) as count'))
                    ->whereNull('deleted_at')->groupBy('tahun','bulan','city_id')->orderBy('bulan')->get();
      $grafiktahunepormascounter = DB::table('epormas_counter')
                    ->select('tahun', DB::raw('SUM(count) as count'))
                    ->whereNull('deleted_at')->groupBy('tahun')->orderBy('tahun')->get();

      $namaBulan = array('Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec');
      $namaBulans = array('Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember');
      $city = EpormasCity::all();
      $category = EpormasCategory::all();
      $countcity = count($city);
      $countcategory = count($category);

      $countgrafikepormascounter = count($grafikepormascounter);
      $countgrafiktahunepormascounter = count($grafiktahunepormascounter);
      $datagrafikepormascounter = [];
      $datagrafiktahunepormascounter = [];
      $datagrafikcountepormascounter = [];
      $datagrafiktotalepormascounter = [];
      $grafikcountepormascounter = [];
      $grafikbulanepormascounter = [];
      $grafiktotalepormascounter = [];
      $grafikpieepormascounter = [];
      for($q=0; $q<$countgrafiktahunepormascounter; $q++){
        $tahungrafikepormascounter = $grafiktahunepormascounter[$q]->tahun;
        $gcountepormascounter  = (int)$grafiktahunepormascounter[$q]->count;
        $totaldataepormascounter = $gcountepormascounter;
        array_push($datagrafiktahunepormascounter,$tahungrafikepormascounter);
        array_push($datagrafikcountepormascounter,$gcountepormascounter);
        array_push($datagrafiktotalepormascounter,$totaldataepormascounter);

        for($kota=1; $kota<=$countcity; $kota++){
            for ($index=0; $index<12; $index++) {
              $grafikcountepormascounter[$q][$kota][$index] = 0;
              $grafikbulanepormascounter[$q][$kota][$index] = 0;
              $grafiktotalepormascounter[$q][$kota][$index] = 0;
              $grafikpieepormascounter[$q][$kota][$index] = ['value'=>0,'name'=>$namaBulan[$index]];
              $totaldatagrafikepormascounter[$q][$index] = 0;
            }
        }
        for($r=0; $r<$countgrafikepormascounter; $r++){
          $tahunsepormascounter = $grafikepormascounter[$r]->tahun;
          $bulansepormascounter = $grafikepormascounter[$r]->bulan;
          $city_id = $grafikepormascounter[$r]->city_id;
          if($bulansepormascounter < 10){
            $bulanepormascounter = substr($bulansepormascounter,1);
          }else {
            $bulanepormascounter = $bulansepormascounter;
          }
          if($tahungrafikepormascounter == $tahunsepormascounter){
              $totaldatagrafikepormascounter[$q][$bulanepormascounter-1] += $grafikepormascounter[$r]->count;
          }
        }
        for($r=0; $r<$countgrafikepormascounter; $r++){
          $tahunsepormascounter = $grafikepormascounter[$r]->tahun;
          $bulansepormascounter = $grafikepormascounter[$r]->bulan;
          $city_id = $grafikepormascounter[$r]->city_id;
          if($bulansepormascounter < 10){
            $bulanepormascounter = substr($bulansepormascounter,1);
          }else {
            $bulanepormascounter = $bulansepormascounter;
          }
          if($tahungrafikepormascounter == $tahunsepormascounter){
              $grafikcountepormascounter[$q][$city_id][$bulanepormascounter-1] = (int)$grafikepormascounter[$r]->count;
              $grafikbulanepormascounter[$q][$city_id][$bulanepormascounter-1] = $grafikepormascounter[$r]->bulan;
              $totaldatagrafikepormascounters = $totaldatagrafikepormascounter[$q][$bulanepormascounter-1];
              $grafiktotalepormascounter[$q][$city_id][$bulanepormascounter-1] = $totaldatagrafikepormascounters;
              $grafikpieepormascounter[$q][$city_id][$bulanepormascounter-1] = ['value'=>$totaldatagrafikepormascounters,'name'=>$namaBulan[$bulanepormascounter-1]];
          }
        }
      }
      $chartgrafikcountepormascounter = ['chartdata'=>$grafikcountepormascounter];
      $datagrafikepormascounter = ['count'=>$grafikcountepormascounter, 'bulan'=>$grafikbulanepormascounter, 'namabulan'=>$namaBulan, 'namabulans'=>$namaBulans, 'kategori'=>$category, 'kota'=>$city, 'total'=>$grafiktotalepormascounter, 'datatahun'=>['tahun'=>$datagrafiktahunepormascounter, 'count'=>$datagrafikcountepormascounter, 'totaldata'=>$datagrafiktotalepormascounter], 'datapie'=>$grafikpieepormascounter, 'seriesdata'=>[$chartgrafikcountepormascounter]];
      //end Pegawai Epormas

        return Response::json(array(
            //Jumlah Penduduk
            'datachartepormascounter' => $datagrafikepormascounter
            //end Jumlah Penduduk
        ));
    }
}
