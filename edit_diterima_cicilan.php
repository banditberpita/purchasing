<?php 
  ob_start();
  SESSION_START(); 
  date_default_timezone_set("Asia/Jakarta");
  $jam_inp  = date("Y-m-d H:i:s");
  include 'header.php';
  include "fungsi/unset_session_pendaftar.php";
  include "fungsi/koneksi.php";

  $no_fisik = $_POST['no_fisik'];

//start update
if(isset($_POST['simpan_mutasi'])){

  if(!empty($_POST['cicil_final'])){
    $cicil_final  = $_POST['cicil_final'];
  }else{
    $cicil_final  = $_POST['cicil_awal'];
  }

  //if((!empty($_POST['usek_update']))||(!empty($_POST['pil_sklh1']))){
    $usek_update  = $_POST['usek_update'];
    $usek_final   = $_POST['usek_final'];
  //}else{
    //$usek_update  = $_POST['usek_final'];
  //}
  
  //if(!empty($_POST['ssr_update'])){
    $ssr_update  = $_POST['ssr_update'];
  //}else{
    //$ssr_update  = $_POST['ssr_update'];
  //}
  
  //if(!empty($_POST['ssp_update'])){
    $ssp_update   = $_POST['ssp_update'];
    $ssp_final   = $_POST['ssp_final'];
  //}else{
    //$ssp_update   = $_POST['ssp_final'];
  //}
  if(!empty($_POST['kode_update'])){
    $kode_update  = $_POST['kode_update'];
  }else{
    $kode_update  = $_POST['kode_final'];
  }
  
  if(!empty($_POST['grade_update'])){
    $grade_update = $_POST['grade_update'];
  }else{
    $grade_update = $_POST['grade_final'];
  }
  
  if(!empty($_POST['trf_dlm'])&&($_POST['trf_dlm'] == 'y')){ 
    $pendaftar_dlm  = ",pendaftar_dalam='y'";
  }else{ 
    $pendaftar_dlm  = ",pendaftar_dalam='n'"; 
  }

  if(!empty($_POST['kelas1_dropdown'])){
    $posisi_awal  = (strpos($_POST['kelas1_dropdown'],"trm"))+3;
    $jml_kata     = strlen($_POST['kelas1_dropdown']);
    $kls_tujuan1  = substr($_POST['kelas1_dropdown'], $posisi_awal, ($jml_kata-$posisi_awal));
    $kelas_update = $kls_tujuan1;
    $klt = ", kelas_tujuan='".$kelas_update."'";
  }
  
  if(!empty($_POST['pil_sklh1'])){
    $sklh_update  = $_POST['pil_sklh1'];
    $skt = ", id_sekolah_diterima='".$sklh_update."'";
  }

  $keterangan  = $_POST['keterangan'];
  $bank  = $_POST['bank'];
  $byr   = $_POST['byr'];

  if(!empty($_POST['eb'])){
    $eb = $_POST['eb'];
  }else{
    $eb = $_POST['eb'];
  }

  //cek data ada tidak
  $qcek    = mysql_query("SELECT * from ppsb_pendaftar where no_form_fisik='".$no_fisik."'", $cn_a) or die ("load cek view pendaftar error");
  $cek     = mysql_fetch_assoc($qcek);   
  
//cek id
  if(!empty($cek['id'])){

  //skip ganerate
  if((!empty($_POST['skip']))&&($_POST['skip']=='x')){
 
    $qupdate = mysql_query("update ppsb_pendaftar set  usek_final='".$usek_update."' ".$klt." ,status_pembayaran='".$bank."', status_pendaftaran='".$byr."', tgl_edit='".date("Y-m-d H:i:s")."', user_edit='".$_SESSION['userid']."' where no_form_fisik='".$no_fisik."'", $cn_a) or die ("simpan pendaftar error");
  //end skip ganerate
  
  //batal masuk
  }else if((!empty($_POST['$sklh_update']))&&($_POST['sklh_update']=='batal_masuk')){

    $qupdcil = mysql_query("update ppsb_cicilan set aksi='d', tgl_aksi='".date("Y-m-d H:i:s")."', user_aksi='".$_SESSION['userid']."' where no_form='".$no_fisik."'", $cn_a) or die ("update cicilan error");

    $qbayar = mysql_query("update ppsb_pembayaran set status_batal='y', modified='".$jam_inp."', modifier='".$_SESSION['userid']."' where no_form='".$no_fisik."' and status_lunas='n'",$cn_a) or die ("update pembayaran Error");

    $qupdate = mysql_query("update ppsb_pendaftar set status_diterima='n', status_diterima_batal='y', flag_tampil='5', tgl_edit='".date("Y-m-d H:i:s")."', user_edit='".$_SESSION['userid']."', keterangan='penerimaan dibatalkan ".$keterangan."' where no_form_fisik='".$no_fisik."'", $cn_a) or die ("simpan pendaftar error");

  }else{


    if((!empty($_POST['pil_sklh1']))&&(!empty($_POST['kelas1_dropdown']))){ 
      $upa=",flag_status_pendaftar=1"; 
      $upb=",flag_proses_penerimaan=1";
      $upc=",flag_tampil=4";
      $upd=",flag_kunci_hasil_rapat=1";
      $ups=",flag_ganerate_slip=1";
    }else{
      $upa=""; 
      $upb="";
      $upc="";
      $upd="";
      $ups="";
    }
    
    function pembulatan($uang){
      $ratusan = substr($uang, -4);
      if($ratusan=='0000'){
        $akhir = $uang;
      }else{
        $akhir = $uang + (10000-$ratusan);
      }
        return $akhir;
    }

  if(((!empty($_POST['cicil_final']))||(!empty($_POST['ssp_update'])))&&($ssp_update>'0')){

    //update cicilan
    $del = mysql_query("delete from ppsb_cicilan where no_form='".$no_fisik."'",$cn_a) or die ("hapus cicilan Error");
    $del = mysql_query("delete from ppsb_pembayaran where no_form='".$no_fisik."' and status_lunas='n'",$cn_a) or die ("hapus cicilan Error");
    $qbayar = mysql_query("update ppsb_pembayaran set status_batal='y', flag_upload=4, modified='".$jam_inp."', modifier='".$_SESSION['userid']."' where no_form='".$no_fisik."' and status_lunas='n'",$cn_a) or die ("update pembayaran Error");
    
    $qbayar = mysql_query("select sum(jml_uang) as jml_uang, max(cicilan_ke) as cicil from ppsb_pembayaran where no_form='".$no_fisik."' and status_lunas='y' ",$cn_a) or die ("update pembayaran Error");
    $dbyr  = mysql_fetch_assoc($qbayar);
    if(!empty($dbyr['cicil'])){
      $akssp=$ssp_update-$dbyr['jml_uang'];
      $ml = $dbyr['cicil']+1;
    }else{
      $akssp=$ssp_update;
      $ml = 1;
    }

    if($ssp_update>'0'){
        for ($i=$ml; $i <= $cicil_final; $i++) { 
          if($cicil_final==3){
            if($i==1){
              $trf = $akssp/2;
            }else if($i==2){
              $trf = ($akssp*0.3);
            }else{
              $trf = ($akssp*0.2);
            }
          }else if($cicil_final==6){
            if($i==1){
              $trf = $akssp*0.25;
            }else{
              $trf = ($akssp*0.15);
            }
          }else if($cicil_final==7){
            if($i==7){
              $trf = $akssp*0.1;
            }else{
              $trf = ($akssp*0.15);
            }
          }else if($cicil_final==8){
            if($i==1){
              $trf = $akssp*0.2;
            }else if($i==2){
              $trf = $akssp*0.2;
            }else{
              $trf = ($akssp*0.1);
            }
          }else{
            $trf = ($akssp/$cicil_final); 
          }
          $ins = mysql_query("insert into ppsb_cicilan (no_registrasi, no_form, no_cicilan, jenis, jml_cicilan, kurs, aksi ,tgl_aksi, usr_aksi) values ('posko','".$no_fisik."', ".$i.", 'SSP', '".$trf."', 'Rp', 'c', '".$date_now."', '".$_SESSION['userid']."')",$cn_a) or die ("Update pendaftar Error");
          
          $hcil=strlen($i);
          if($hcil==1){ $no_cicil="0".$i; }else{ $no_cicil=$i; }
          $no_slip = "00692".$no_fisik."".$no_cicil."19";

          $qbayar = mysql_query("insert into ppsb_pembayaran (no_registrasi, no_form, no_slip, cicilan_ke, jml_uang, status_lunas, status_batal, status_otorisasi, jenis, date_time, creator, flag_insert) VALUES ('posko', '".$no_fisik."', '".$no_slip."', ".$i.", '".$trf."', 'n', 'n', 'y', 'SSP', '".$jam_inp."', '".$_SESSION['userid']."', '7')",$cn_a) or die ("update pembayaran Error");

          //for ($i=1; $i <= $cicil_final; $i++) { 
        }
        //if($ssp_update>'0'){
      }
      //if(((!empty($_POST['cicil_final']))||(!empty($_POST['ssp_update'])))&&($ssp_update>'0')){
      }

      if(!empty($cek['ssr'])){
      $no_slip = "00692".$no_fisik."0119";  
      $qssr = mysql_query("insert into ppsb_pembayaran (no_registrasi, no_form, no_slip, cicilan_ke, jml_uang, status_lunas, status_batal, status_otorisasi, jenis, date_time, creator, flag_insert, flag_update) VALUES ('posko', '".$no_fisik."', '".$no_slip."', '1', '".$cek['ssr']."', 'n', 'n', 'y', 'SSR', '".$jam_inp."', '".$_SESSION['userid']."', '".$fi."', '".$fu."')",$cn_a) or die ("update pembayaran ssr Error");
    }

    //created cicilan
    if(($kode_update=='F')||($kode_update=='F0')||($kode_update=='F1')||($kode_update=='p')){
      if(($kode_update=='F')&&($kelas_update=='1*')){
        $trfusk = pembulatan((($usek_update*100)/15)*0.25) ;
      }else{
        $trfusk = pembulatan($usek_update) ;
      }
      //$trfssp = '0' ;
    }else if(($kode_update=='N0')||($kode_update=='B')||($kode_update=='N')||($kode_update=='A')||($kode_update=='A0')||($kode_update=='A1')||($kode_update=='D')||($kode_update=='D1')||($kode_update=='E')||($kode_update=='E1')){
      if ($kode_update=='A') {
        if($usek_update<350000){
          $trfusk = 350000;
        }else{
          $trfusk = pembulatan($usek_update) ;
        }
      }else{
        $trfusk = pembulatan($usek_update) ;
      }
      //$trfssp = $upd['ssp_final'];
    }else{
      $trfusk = pembulatan($usek_update);
      //$trfssp = $upd['ssp_final'];
    }

    //update pendaftar
    $qupdate = mysql_query("update ppsb_pendaftar set early_bird='".$eb."', jml_cicilan=".$cicil_final.", kode_pengisian='".$kode_update."', ssr='".$ssr_update."', ssp_final='".$ssp_update."', usek_final='".$usek_update."', status_pembayaran='".$bank."', status_pendaftaran='".$byr."', grade_test='".$grade_update."', tgl_edit='".date("Y-m-d H:i:s")."', user_edit='".$_SESSION['userid']."', keterangan='".$keterangan."' ".$skt." ".$klt." ".$upa." ".$upb." ".$upc." ".$upd." ".$ups." ".$pendaftar_dlm."  where no_form_fisik='".$no_fisik."'", $cn_a) or die ("simpan pendaftar error");

  //end skip ganerate
  }
  
  }
//end cek id
}

//tambah cicilan
if(isset($_POST['simpan_cicilan'])){
    
    $jml_uang = $_POST['jml_uang'];
    $cil_ke   = $_POST['cicilke'];
    $jenis    = $_POST['jenis'];
    $awal_byr = $_POST['tgl_awal'];
    $akhir_byr= $_POST['tgl_akhir'];

    if($jenis=='SSP'){
      $qinpcil = mysql_query("insert into ppsb_cicilan set no_registrasi='posko', no_form='".$no_fisik."', no_cicilan='".$cil_ke."', jenis='".$jenis."', jml_cicilan='".$jml_uang."', kurs='Rp', aksi='c', tgl_aksi='".date("Y-m-d H:i:s")."', usr_aksi='".$_SESSION['userid']."'", $cn_a) or die ("insert data cicilan error");
      if($qinpcil){
        $qupdate = mysql_query("update ppsb_pendaftar set jml_cicilan='".$cil_ke."' where no_form_fisik='".$no_fisik."'", $cn_a) or die ("load data pendaftar error");
      }
    }

      $hcil=strlen($cil_ke);
      if($hcil==1){ $no_cicil="0".$cil_ke; }else{ $no_cicil=$cil_ke; }
      $no_slip = "00692".$no_fisik."".$no_cicil."19";
      $qcstt = mysql_query("select id from ppsb_pembayaran where no_slip='".$no_slip."' and jenis='".$jenis."' and status_batal='n'",$cn_a) or die ("cek data pembayaran Error");
      $cstt  = mysql_num_rows($qcstt);
      if(empty($cstt)){
        $qbayar = mysql_query("insert into ppsb_pembayaran (no_registrasi, no_form, no_slip, cicilan_ke, jml_uang, tgl_start_bayar, tgl_jatuh_tempo, status_lunas, status_batal, status_otorisasi, jenis, date_time, creator, flag_insert) VALUES ('posko', '".$no_fisik."', '".$no_slip."', '".$cil_ke."', '".$jml_uang."', '".$awal_byr."', '".$akhir_byr."', 'n', 'n', 'y', '".$jenis."', '".$jam_inp."', '".$_SESSION['userid']."', '1')",$cn_a) or die ("update pembayaran Error");
      }
//end update

}else if(isset($_POST['update_cicilan'])){
    $id       = $_POST['id'];
    $jml_uang = $_POST['jml_uang'];
    $cicilke  = $_POST['cicilke'];
    $jenis    = $_POST['jenis'];
    $awal_byr = $_POST['tgl_awal'];
    $akhir_byr= $_POST['tgl_akhir'];
    if(!empty($_POST['gb_ccl'])){ $gb_ccl = $_POST['gb_ccl']; }
   
    if($jenis<>'SSR'){
      $qupdcil = mysql_query("update ppsb_cicilan set jml_cicilan='".$jml_uang."' where no_cicilan='".$cicilke."' and no_form='".$no_fisik."'", $cn_a) or die ("update cicilan error");
    }

    if($jenis=='SSR'){
        $qcstt = mysql_query("select id from ppsb_pembayaran where id='".$id."' and jenis='SSR' and status_lunas='n' and status_batal='n'",$cn_a) or die ("cek data pembayaran Error");
        $cstt  = mysql_fetch_assoc($qcstt);
        if(!empty($cstt['id'])){

          $hcil=strlen($gb_ccl);
          if($hcil==1){ $no_cicil="0".$gb_ccl; }else{ $no_cicil=$gb_ccl; }
          $no_slip = "00692".$no_fisik."".$no_cicil."19";

          $qsrup = mysql_query("update ppsb_pembayaran set no_slip='".$no_slip."', jml_uang='".$jml_uang."', tgl_start_bayar='".$awal_byr."', tgl_jatuh_tempo='".$akhir_byr."', modified='".$jam_inp."', modifier='".$_SESSION['userid']."', flag_upload='0' where id='".$id."' and jenis='SSR' and status_lunas='n' and status_batal='n'",$cn_a) or die ("update pembayaran Error");
        }
    }else{
      $qbayar = mysql_query("update ppsb_pembayaran set jml_uang='".$jml_uang."', tgl_start_bayar='".$awal_byr."', tgl_jatuh_tempo='".$akhir_byr."', modified='".$jam_inp."', modifier='".$_SESSION['userid']."', flag_upload='0' where id='".$id."'",$cn_a) or die ("update pembayaran Error");
    }
}else if(isset($_POST['hapus'])){
    $id  = $_POST['id'];
    $qck = mysql_query("select * from ppsb_pembayaran where id='".$id."' and status_lunas='n'",$cn_a) or die ("hapus cicilan Error");
    $ck  = mysql_fetch_assoc($qck);
    if(!empty($ck['id'])){  
      $qcil = mysql_query("delete from ppsb_cicilan where no_cicilan='".$cicilke."' and no_form='".$no_fisik."'", $cn_a) or die ("update cicilan error");
      $qpel = mysql_query("update ppsb_pembayaran set status_batal='y', modified='".$jam_inp."', modifier='".$_SESSION['userid']."' where id='".$id."' and status_lunas='n'",$cn_a) or die ("update pembayaran Error");
    }
}



  $qdata    = mysql_query("SELECT * from ppsb_pendaftar where no_form_fisik='".$no_fisik."'", $cn_a) or die ("load data view pendaftar error");
  $data     = mysql_fetch_assoc($qdata);  
  if((!empty($id))&&($cek_sisa!='-')){
    $filA="and id<>".$id."";
  }else{
    $filA="";
  }

  $qcek = mysql_query("select sum(jml_cicilan) as cicilan from ppsb_cicilan where no_form='".$no_fisik."' ".$filA." ", $cn_a) or die ("load data cicilan error");
  $cek  = mysql_fetch_assoc($qcek);

  $qpem = mysql_query("SELECT sum(jml_uang) as ssp_byr from ppsb_pembayaran where no_form='".$no_fisik."' and status_batal='n' and jenis='SSP'", $cn_a) or die ("query pembayaran error");
  $pem  = mysql_fetch_assoc($qpem);

  if(!empty($data['ssp_final'])){ 
    $ssp_in=$data['ssp_final']; 
    $sisa_ssp_fin = $ssp_in - $pem['ssp_byr'];
  }else if(!empty($data['ssp_daftar'])){ 
    $ssp_in=$data['ssp_daftar'];
    $sisa_ssp_fin = $ssp_in - $pem['ssp_byr']; 
  }

function rupiah($angka){
  $hasil_rupiah = number_format($angka,0,',','.');
  return $hasil_rupiah;
}

?>

<style type="text/css">
  hr {
  -moz-border-bottom-colors: none;
  -moz-border-image: none;
  -moz-border-left-colors: none;
  -moz-border-right-colors: none;
  -moz-border-top-colors: none;
  border-color: #EEEEEE -moz-use-text-color #FFFFFF;
  border-style: solid none;
  border-width: 1px 0;
  margin: 18px 0;
}
</style>

<!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>List<small>Data Pendaftar</small></h1>
    <ol class="breadcrumb">
      <li><a href="#"><i class="fa fa-dashboard"></i>edit cicilan</a></li>
    </ol>
  </section>

  <!-- Main content -->
  <section class="content">
  <!-- Small boxes (Stat box) -->
    <div class="row">
      <div class="col-xs-12">
        <div class="box">
            <div class="box-header">
              <h3 class="box-title">
                <p class="text-yellow"><?php echo $ket; ?></p>
              </h3>
            </div>
            <!-- /.box-header -->
            
            <div class="box-body">
              <form class="form-horizontal" method="post" action="<?php echo $base_url ?>edit_cicilan.html"name="postform" >
              <div class="row col-xs-6">
                <div class="form-group">
                    <label for="staticEmail" class="col-sm-3 col-sm-offset-2 control-label">No Fisik Form</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="no_fisik" type="text" id="form" size="7" value="<?php if(!empty($no_fisik)){ echo $no_fisik; } ?>" readonly />
                    </div>
                 </div>
                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-3 col-sm-offset-2 control-label">Nama</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" value="<?php echo $data['nama']; ?>" readonly />
                    </div>
                 </div>
                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-3 col-sm-offset-2 control-label">Grade</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" value="<?php echo $data['grade_test']; ?>" readonly />
                    </div>
                 </div>
                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-3 col-sm-offset-2 control-label">Kode</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" value="<?php echo $data['kode_pengisian']; ?>" readonly />
                    </div>
                 </div>
                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-3 col-sm-offset-2 control-label">Cicilan</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" size="7" value="<?php echo $data['jml_cicilan']; ?>" readonly />
                    </div>
                 </div>
              </div>

              <div class="row col-xs-6">
                 
                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 control-label">SSP Daftar</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" value="<?php echo rupiah($data['ssp_daftar']); ?>" readonly />
                    </div>
                 </div>

                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 control-label">SSP Final</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" value="<?php echo rupiah($data['ssp_final']); ?>" readonly />
                    </div>
                 </div>

                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 control-label">SSP Sisa</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" value="<?php echo rupiah($sisa_ssp_fin); ?>" readonly />
                    </div>
                 </div>

                 <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 control-label">SSR</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="bank" type="text" id="form" value="<?php echo rupiah($data['ssr']); ?>" readonly />
                    </div>
                 </div>
              </div>
            
            </div>

              <?php if((!isset($_POST['edit']))&&(!isset($_POST['tambah']))){ ?>
                 <div class="box-body col-xs-9 col-sm-offset-2">
                    <div class="box-header">
                      <h3 class="box-title">
                        <button type="submit" name="tambah" class="btn btn-primary btn-sm btn-social btn-instagram"><i class="fa fa-plus"></i>data</button>
                      </h3>
                      <div class="box-header">
                      <h3 class="box-title">--SSR--</h3>
                    </div>
                    </div>
                    <table class="table table-bordered">
                      <tbody>
                        <tr>
                          <th><center>Cicilan</center></th>
                          <th><center>No VA</center></th>
                          <th><center>Jenis</center></th>
                          <th><center>Nominal</center></th>
                          <th><center>Jatuh Tempo</center></th>

                          <th style="width:20%;"><center>Action</center></th>
                        </tr>
                        <?php
                          $qcc = mysql_query("SELECT * from ppsb_pembayaran where no_form='".$no_fisik."' and status_batal='n' and jenis='SSP' order by cicilan_ke asc", $cn_a) or die ("query cicilan error");
                          while($cdata = mysql_fetch_assoc($qcc)){
                        ?>
                        <tr>
                          <td><center><?php echo $cdata['cicilan_ke']; ?></center></td>
                          <td><center><?php echo $cdata['no_slip']; ?></center></td>
                          <td><center><?php echo $cdata['jenis']; ?></center></td>
                          <td><center><?php echo rupiah($cdata['jml_uang']); ?></center></td>
                          <td><center><?php echo $cdata['tgl_start_bayar']."/".$cdata['tgl_jatuh_tempo']; ?></center></td>
                          <td><center>
                            <div class="btn-group">
                              <form class="form-horizontal" method="post" action="<?php echo $base_url ?>edit_cicilan.html"name="postform" >
                              <input type="hidden" name="id" value="<?php echo $cdata['id']; ?>" />
                              <input class="col-sm-4 form-control" name="no_fisik" type="hidden" id="form" value="<?php echo $no_fisik; ?>" readonly />
                              <button type="submit" name="edit">Edit</button>
                              <button type="submit" name="hapus" onclick="return confirm('Are you sure?');">hapus</button>
                              </form>
                            </div>
                            </center>
                          </td>
                        </tr>
                      <?php } ?>
                      </tbody>  
                    </table>
                    <div class="box-header">
                      <h3 class="box-title">--SSR--</h3>
                    </div>
                    <table class="table table-bordered">
                      <tbody>
                        <tr>
                          <th><center>Cicilan</center></th>
                          <th><center>No VA</center></th>
                          <th><center>Jenis</center></th>
                          <th><center>Nominal</center></th>
                          <th><center>Jatuh Tempo</center></th>

                          <th style="width:20%;"><center>Action</center></th>
                        </tr>
                        <?php
                          $qcicil = mysql_query("SELECT * from ppsb_pembayaran where no_form='".$no_fisik."' and status_batal='n' and jenis='SSR' order by cicilan_ke asc", $cn_a) or die ("query cicilan error");
                          while($cicil = mysql_fetch_assoc($qcicil)){
                        ?>
                        <tr>
                          <td><center><?php echo $cicil['cicilan_ke']; ?></center></td>
                          <td><center><?php echo $cicil['no_slip']; ?></center></td>
                          <td><center><?php echo $cicil['jenis']; ?></center></td>
                          <td><center><?php echo rupiah($cicil['jml_uang']); ?></center></td>
                          <td><center><?php echo $cicil['tgl_start_bayar']."/".$cicil['tgl_jatuh_tempo']; ?></center></td>
                          <td><center>
                            <div class="btn-group">
                              <form class="form-horizontal" method="post" action="<?php echo $base_url ?>edit_cicilan.html"name="postform" >
                              <input type="hidden" name="id" value="<?php echo $cicil['id']; ?>" />
                              <input name="no_fisik" type="hidden" id="form" value="<?php echo $no_fisik; ?>" readonly />
                              <button type="submit" name="edit">Edit</button>
                              <button type="submit" name="hapus" onclick="return confirm('Are you sure?');">hapus</button>
                            </form>
                            </div>
                            </center>
                          </td>
                        </tr>
                      <?php } ?>
                      </tbody>  
                    </table>

                 </div>
              <?php }else if((isset($_POST['edit']))||(isset($_POST['tambah']))){  
                //if(isset($_POST['edit'])){  
                  $id   = $_POST['id'];
                  $qcil = mysql_query("select * from ppsb_pembayaran where id='".$id."'", $cn_a) or die ("query pembayaran error");
                  $cil  = mysql_fetch_assoc($qcil);
                  $no_fisik = $_POST['no_fisik'];
                //}
              ?>
                
                <form class="form-horizontal" method="post" action="<?php echo $base_url ?>edit_cicilan.html" name="postformx" >
                  
                  <div class="row col-xs-12"> <!-- modal body --><hr>
                  <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 col-sm-offset-2 control-label">Input Cicilan</label>
                    <div class="col-sm-6">
                      <input class="form-control" name="jml_uang" type="text" id="cicil_inp" value="<?php echo $cil['jml_uang']; ?>" onkeyup="document.getElementById('usek_disp').innerHTML = formatCurrency(this.value);" />
                    </div>
                    <input class="col-sm-4 form-control" name="id" type="hidden" id="form" value="<?php echo $id; ?>" readonly />
                    <input class="col-sm-4 form-control" name="cicilke" type="hidden" id="form" value="<?php if(!empty($cil['cicil_ke'])){ echo $cil['cicil_ke'] ; }else{ echo '1'; } ?>" readonly />
                    <input class="col-sm-4 form-control" name="no_fisik" type="hidden" id="form" value="<?php echo $no_fisik; ?>" readonly />
                  <!--<label id="usek_disp" class="col-sm-2 control-label" style="text-align: left"></label> -->
                  </div>
                  <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 col-sm-offset-2 control-label">Awal Bayar</label>
                    <div class="col-sm-6" >
                      <div class="input-group date">
                        <div class="input-group-addon">
                          <i class="fa fa-calendar"></i>
                        </div>
                        <input type="text" id="tgl_awal" name="tgl_awal" class="form-control pull-right datepicker" value="<?php if($cil['tgl_start_bayar']>1){ echo $cil['tgl_start_bayar']; } ?>" >
                      </div>                    
                    </div>
                  </div>

                   <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 col-sm-offset-2 control-label">Jenis</label>
                    <div class="col-sm-6">
                      <select id="jenis" name="jenis" class="form-control" <?php if(isset($_POST['edit'])){ echo 'readonly'; } ?> >
                        <?php
                          $qgrd = array('SSP','SSR');
                          foreach($qgrd as $lva){
                            if($lva==$cil['jenis']){
                              echo "<option value=$lva selected >$lva</option>";
                            }else{
                              echo "<option value=$lva>$lva</option>";
                            }
                          } 
                        ?>
                      </select>
                    </div>
                  </div>
                  <?php
                  if($cil['jenis']=='SSR'){
                  ?>
                  <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 col-sm-offset-2 control-label">Cicilan ke</label>
                    <div class="col-sm-6">
                      <select id="gb_ccl" name="gb_ccl" class="form-control" >
                        <?php
                          //$qgb = mysql_query("select cicilan_ke from ppsb_pembayaran where no_form='".$no_fisik."' and status_batal='n' group by cicilan_ke", $cn_a) or die ("query pembayaran error");
                          //while($gb = mysql_fetch_assoc($qgb)){
                        for ($i=1; $i <= 8 ; $i++) {
                            if($i==$cil['cicilan_ke']){ ?>
                             <option value='<?php echo $i; ?>' selected ><?php echo $i; ?></option>
                            <?php }else{ ?>
                              <option value='<?php echo $i; ?>' ><?php echo $i; ?></option>
                            <?php 
                            }
                          } 
                        ?>
                      </select>
                    </div>
                  </div>
                <?php } ?>

                  <div class="form-group">
                    <label for="staticEmail" class="col-sm-2 col-sm-offset-2 control-label">Akhir Bayar</label>
                    <div class="col-sm-6" >
                      <div class="input-group date">
                        <div class="input-group-addon">
                          <i class="fa fa-calendar"></i>
                        </div>
                        <input type="text" id="tgl_akhir" name="tgl_akhir" class="form-control pull-right datepicker" value="<?php if($cil['tgl_jatuh_tempo']>1){ echo $cil['tgl_jatuh_tempo']; } ?>" >
                      </div>
                    </div>
                  </div>
                <?php 
                  if(isset($_POST['edit'])){ 
                    $pil="update_cicilan";
                  }else if(isset($_POST['tambah'])){
                    $pil="simpan_cicilan";
                  } 
                ?>

                <div class="row col-sm-12"> <!-- modal body -->
                  <br><br>
                  <div class="form-group">
                    <div class="col-sm-2 col-sm-offset-4">
                      <button type="submit" class="btn btn-block btn-warning btn-lg" name="batal">batal</button>
                    </div>
                    <div class="col-sm-2">
                      <button type="submit" class="btn btn-block btn-primary btn-lg" name="<?php echo $pil; ?>"><i class="fa fa-save"></i> <?php echo $pil; ?></button>
                    </div>
                  </div>
                </div>
              <?php }  ?>

               </form>  


                <?php if((!isset($_POST['edit']))&&(!isset($_POST['tambah']))){ ?>
                <form class="form-horizontal" role="form" method="post" action="diterima_home.html" name="postform" >
                <div class="form-group">
                    <input class="form-control" name="no_fisik" type="hidden" id="form" size="7" value="<?php if(!empty($no_fisik)){ echo $no_fisik; } ?>" readonly />
                    <div class="col-sm-2 col-sm-offset-5">
                        <button type="submit" name='simpan_mutasix' class="btn btn-block btn-primary btn-sm" data-toggle="tooltip" data-placement="top" <?php if(($sisa_ssp_fin!='0')&&($data['ssp_final']!='0')){ echo 'title="sisa tagihan harus 0" disabled'; } ?> >
                            <i class="fa fa-save"></i> Simpan
                        </button>
                    </div>
                </div>
                </form>
              <?php } ?>
            </div>
            <!-- /.box-body -->
          
        </div>
        <!-- /.box -->
      </div>
      <!-- /.col -->
    </div>
<!-- /.row -->  
  </section>
<!-- /.content-header -->

<?php include 'footer.php'; ?>
