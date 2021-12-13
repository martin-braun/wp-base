<?php
/**
 * Email Order Shipment details
 *
 * This template can be overridden by copying it to yourtheme/woocommerce-germanized/emails/email-order-shipment-details.php.
 *
 * HOWEVER, on occasion Germanized will need to update template files and you
 * (the theme developer) will need to copy the new files to your theme to
 * maintain compatibility. We try to do this as little as possible, but it does
 * happen. When this occurs the version of the template file will be bumped and
 * the readme will list any important changes.
 *
 * @see https://github.com/vendidero/woocommerce-germanized/wiki/Overriding-Germanized-Templates
 * @package Germanized/Shipments/Templates/Emails
 * @version 1.0.0
 */
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

$text_align = is_rtl() ? 'right' : 'left';
$count = 0;
?>
<table id="tracking" cellspacing="0" cellpadding="0" style="width: 100%; vertical-align: top; margin-bottom: 40px; padding:0;" border="0">
	<tr>
		<td style="text-align:<?php echo esc_attr( $text_align ); ?>; font-family: 'Helvetica Neue', Helvetica, Roboto, Arial, sans-serif; border:0; padding:0;" valign="top">
            <?php foreach( $shipments as $key => $shipment ) : $count++; ?>
                <?php if ( sizeof( $shipments ) > 1 ) : ?>
                    <h2><?php printf( _x( 'Shipment %d of %d', 'shipments', 'woocommerce-germanized' ), $count, sizeof( $shipments ) ); ?></h2>
                <?php endif; ?>

	            <?php if ( $shipment->get_est_delivery_date() ) : ?>
                    <p class="est-delivery-date"><?php _ex(  'Estimated date:', 'shipments', 'woocommerce-germanized' ); ?> <span class="date"><?php echo wc_format_datetime( $shipment->get_est_delivery_date(), wc_date_format() ); ?></span></p>
	            <?php endif; ?>

                <?php if ( $shipment->has_tracking() ) : ?>
                    <?php if ( $shipment->get_tracking_url() ) : ?>
                        <p class="tracking-button-wrapper"><a class="button email-button btn" href="<?php echo esc_url( $shipment->get_tracking_url() ); ?>"><?php _ex(  'Track your shipment', 'shipments', 'woocommerce-germanized' ); ?></a></p>
                    <?php endif; ?>

                    <?php if ( $shipment->has_tracking_instruction() ) : ?>
                        <p class="tracking-instruction"><?php echo $shipment->get_tracking_instruction(); ?></p>
                    <?php endif; ?>
                <?php else: ?>
                    <p class="tracking-instruction"><?php _ex( 'Sorry, this shipment does currently not support tracking.', 'shipments', 'woocommerce-germanized' ); ?></p>
	            <?php endif; ?>
            <?php endforeach; ?>
		</td>
	</tr>
</table>
