<?php

# Seguridad
defined('INDEX_DIR') OR exit('was software says .i.');
use Mike42\Escpos\Printer;
use Mike42\Escpos\PrintConnectors\WindowsPrintConnector;
use Mike42\Escpos\EscposImage;
//------------------------------------------------

final class Prt {
  final public function printComanda(array $data) {
        try {
            $connector = new WindowsPrintConnector($data['zonas']['zc_print']);
            $printer = new Printer($connector);
            $printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
            $printer -> setJustification(Printer::JUSTIFY_CENTER);
            $printer -> setTextSize(2,2);
            $printer -> text($data['zonas']['zc_name']."\n");
            $printer -> text("CODIGO:".str_pad($data['pedido']['id'], 5, '0', STR_PAD_LEFT)."\n");
            $printer -> text("MESA:".$data['pedido']['pe_mesa']."\n");
            $printer -> selectPrintMode();
            $printer -> feed();
            $printer -> setTextSize(2,1);
            $printer -> setJustification(Printer::JUSTIFY_LEFT);
            $printer -> text("FECHA:".$data['pedido']['pe_date']."\n");
            $printer -> text("HORA:".$data['pedido']['pe_time']."\n");
            $printer -> text("MOSO:".$data['pedido']['pe_moso']."\n");
            $printer -> text("---------------------------------\n");
            $printer -> text("CANT  DETALLE                    \n");
            $printer -> selectPrintMode($printer::MODE_FONT_B);
            $printer -> setTextSize(1,2);
            foreach ($data['zonas']['ordenes'] as $ve) {
              $printer -> text("  ".$ve['pp_cant']."    ".$ve['pp_producto']."\n");
            }
            $printer -> text("-----------------------------------\n");
            $printer -> selectPrintMode();
            $printer -> feed();
            $printer -> feed();
            $printer -> cut();
            $printer ->close();
            return ['success'=>1,'message'=>'Comandas impresas'];
        } catch (Exception $e) {
            return ['success'=>0,'message'=>$e->getMessage()];
        }
  }

  final public function printVenta(array $data):array{
        ##--------------------------------------------------------------------------------------
        try{
            for($i=0;$i<NCOPIASTVENTA;$i++){
              $this->subTicketVenta($data);
            }
            return ['success'=>1,'message'=>'Ticket impreso'];
         } catch (Exception $e) {
            return ['success'=>0,'message'=>$e->getMessage()];
         }   
        ##--------------------------------------------------------------------------------------
  }

  final public function printCuenta(array $data):array{
       try {
          $total =0;
          $connector = new WindowsPrintConnector($data['emp']['e_print']);
          $printer   = new Printer($connector);
          $printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
          $printer -> setJustification(Printer::JUSTIFY_CENTER);
          $printer -> setTextSize(2,2);
          $printer -> text("PRE - CUENTA\n");
          $printer -> text("(".$data['venta']['ped_mesa'].")\n");
          $printer -> selectPrintMode();
          $printer -> feed();
          $printer -> setJustification(Printer::JUSTIFY_LEFT);
          $printer -> setTextSize(2,1);
          $printer -> text("CODIGO:".str_pad($data['venta']['id'], 5, '0', STR_PAD_LEFT)."\n");
          $printer -> text("FECHA:".$data['venta']['ped_date']."\n");
          $printer -> text("MOSO:".$data['venta']['ped_moso']."\n");
          $printer -> feed();
          $printer -> text("CANT PU DETALLE \n");
          $printer -> text("------------------------\n");
          $printer -> selectPrintMode($printer::MODE_FONT_B);
          $printer -> setJustification(Printer::JUSTIFY_LEFT);
          $printer -> setTextSize(2,1);

          foreach ($data['child'] as $ve) {
            $printer -> text("  ".$ve['pp_cant']."   ".$ve['pp_precio']."   ".$ve['pp_name']."\n");
            $total += (intval($ve['pp_cant'])*floatval($ve['pp_precio']));
          }
          $printer -> selectPrintMode();
          $printer -> setTextSize(2,1);
          $printer -> text("------------------------\n");
          $printer -> selectPrintMode();
          $printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
          $printer -> setTextSize(2,2);
          $printer -> text("TOTAL: S/ ".$total."\n");
          $printer -> selectPrintMode();
          $printer -> feed();
           $printer -> cut();
          $printer -> close();
           return ['success'=>1,'message'=>'Se ha enviado la impresión'];
        } catch (Exception $e) {
             return ['success'=>0,'message'=>$e->getMessage()];
        }
  }
  
  private final function subTicketVenta(array $data){
        $connector = new WindowsPrintConnector($data['emp']['e_print']);
        $printer = new Printer($connector);
        $printer -> selectPrintMode(Printer::MODE_DOUBLE_HEIGHT | Printer::MODE_DOUBLE_WIDTH);
        $printer -> setJustification(Printer::JUSTIFY_CENTER);
        $printer -> setTextSize(2,2);
        if(PRINTIMAGES) $logo = EscposImage::load('images/logo.png');
        $printer->setJustification(Printer::JUSTIFY_CENTER);
        if(PRINTIMAGES) $printer->bitImage($logo); //bitImage
        $printer -> feed();
        $printer -> text($data['emp']['e_name']."\n");
        $printer -> selectPrintMode();
        $printer -> feed();
        $printer -> setTextSize(2,1);
        $printer -> setEmphasis(true);
        $printer -> text("RUC: ".$data['emp']['e_ruc']."\n");
        $printer -> setEmphasis(false);
        $printer -> selectPrintMode();
        $printer -> text($data['emp']['e_dist']."-".$data['emp']['e_prov']."-".$data['emp']['e_depa']."\n");
        $printer -> text($data['emp']['e_street']."\n");
        $printer -> text("TEL.: ".$data['emp']['e_phono']."\n");
        $printer -> feed();
        $printer -> setTextSize(1,1); 
        $printer -> selectPrintMode($printer::MODE_EMPHASIZED);
        $printer -> setJustification(Printer::JUSTIFY_CENTER);
        $printer -> setEmphasis(true);
        if($data['venta']['ped_sunat']==1){
               $printer -> text($data['venta']['ped_doc'].' ELECTRONICA '.$data['venta']['ped_serie'].'-'.$data['venta']['ped_number']."\n");
        }else{
           if($data['venta']['fac_modo']==0){
               $printer -> text($data['venta']['ped_doc'].'  '.$data['venta']['ped_serie'].'-'.$data['venta']['ped_number']."\n");
            }else{
              $printer -> text('TICKET '.$data['venta']['ped_hnumber']."\n");  
            }
        }
        $printer -> setEmphasis(false);
        $printer -> feed();
        $printer -> selectPrintMode();
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> text("FECHA    : ".$data['venta']['ped_date']."\n");
        $printer -> text("MESA     : ".$data['venta']['ped_mesa']."\n");
        $printer -> text("CLIENTE  : ".$data['venta']['ped_cli_name']."\n");
        $printer -> text("DNI/RUC  : ".$data['venta']['ped_cli_doc']."\n");
        $printer -> text("DIRECCION: ".(empty($data['venta']['ped_cli_street']) ? "---" :$data['venta']['ped_cli_street'])."\n");
        $printer -> selectPrintMode();
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> setTextSize(1,1);
        $printer -> text("------------------------------------------------\n");
        if($data['detallado']==false){
           $printer -> text("POR CONSUMO\n");
        }else{
          $printer -> setEmphasis(true);
          $printer -> text("CANT  DETALLE          PU    PT\n");
          $printer -> setEmphasis(false);
          $printer -> selectPrintMode($printer::MODE_FONT_A);
            $printer -> setTextSize(1,1);
          foreach ($data['child'] as $ve) {
            $printer -> text("  ".$ve['pp_cant']."   ".$ve['pp_name']."       ".$ve['pp_precio']."   ".$ve['pp_precio_calculado']."\n");
          }
        }
        $printer -> text("------------------------------------------------\n");
        $printer -> selectPrintMode();
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        $printer -> text("Sub Total      : ".$data['venta']['ped_precio_sub']."\n");
        $printer -> text("Op. gravadas   : ".$data['venta']['ped_op_gravadas']."\n");
        $printer -> text("Op. Inafectas  : ".$data['venta']['ped_op_inafectas']."\n");
        $printer -> text("Op. Exoneradas : ".$data['venta']['ped_op_exoneradas']."\n");
        $printer -> text("IGV            : ".$data['venta']['ped_impuesto']."\n");
        $printer -> setEmphasis(true);
        $printer -> text("TOTAL    S/.    ".$data['venta']['ped_precio']."\n");
        $printer -> setEmphasis(false);
        $printer -> feed();
        $printer -> text("PAGO CON          : S/ ".$data['venta']['ped_importe']."\n");
        $printer -> text("VUELTO            : S/ ".$data['venta']['ped_vuelto']."\n");
        $printer -> feed();
        $printer -> selectPrintMode();
        $printer -> setTextSize(1,1);
        $printer -> feed();
        $printer -> setJustification(Printer::JUSTIFY_LEFT);
        if($data['venta']['ped_sunat']==1){
            $printer -> text(" SON :".$data['venta']['ped_letras']."\n");
            $printer -> text(" RESUMEN :".$data['venta']['ped_hash']."\n");
            $printer -> feed();
            if(PRINTQR and PRINTIMAGES){
                \PHPQRCode\QRcode::png($data['emp']['e_ruc'].'|'.$data['venta']['ped_hdoc'].'|'.$data['venta']['ped_serie'].'|'.$data['venta']['ped_number'].'|'.$data['venta']['ped_impuesto'].'|'.$data['venta']['ped_precio'].'|'.$data['venta']['ped_date'].'|'.'|'.'|'.$data['venta']['ped_hash'],'images/qr.png','L', 8, 4);
                $qr = EscposImage::load('images/qr.png',false);
                $printer->setJustification(Printer::JUSTIFY_CENTER);
                $printer->bitImage($qr);
                unlink('images/qr.png');
            }
        }
        $printer -> feed();
        $printer -> text("ATENDIDO POR: ".$data['venta']['ped_moso']."\n");
        $printer -> feed();
        $printer -> text($data['venta']['ped_obs']."\n");
        $printer -> feed();
        $printer -> text(LEGEND."\n");
        $printer -> feed();
        $printer -> text(FRASEFOOTER."\n");
        $printer -> text("------------------------------------------\n");
        $printer->cut();
        $printer ->close();
  }
}
?>
