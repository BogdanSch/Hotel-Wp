<?php

namespace MPHB;

/**
 * @since 3.6.0
 * @since 3.6.0 MPHB\Downloader was replaced with MPHB\ActionsHandler.
 * @since 3.6.0 method doActions() was replaced with doEarlyActions() and doLateActions().
 */
class ActionsHandler
{
    public function __construct()
    {
        // Early actions: do before full initialization of the plugin
        add_action('init', array($this, 'doEarlyActions'), 4);

        // Late action: wait for the plugins when it initialize more components
        add_action('init', array($this, 'doLateActions'), 1004);
    }

    /**
     * @since 3.6.0
     */
    public function doEarlyActions()
    {
        if (!isset($_GET['mphb_action'])) {
            return;
        }

        switch ($_GET['mphb_action']) {
            case 'download': $this->maybeDownload(); break;
        }
    }

    /**
     * @since 3.6.0
     */
    public function doLateActions()
    {
        if (!isset($_GET['mphb_action'])) {
            return;
        }

        switch ($_GET['mphb_action']) {
            // Requires gateways and API to initialize first
            case 'force_upgrade': $this->forceUpgrader(); break;
            case 'update_confirmation_endpoints': $this->updateConfirmationEndpoints(); break;
            case 'hide_notice': $this->hideNotice(); break; 
        }
    }

    protected function maybeDownload()
    {
        $filename = isset( $_GET['filename'] ) ? sanitize_text_field( wp_unslash( $_GET['filename'] )) : '';

        if (!mphb_verify_nonce("mphb_download-{$filename}")) {
            $this->fireError(__('Nonce verification failed.', 'motopress-hotel-booking'));
        }

        $file = mphb_uploads_dir() . $filename;

        if (empty($filename) || !file_exists($file)) {
            $this->fireError(__('The file does not exist.', 'motopress-hotel-booking'));
        }

        $removeAfter = !isset($_GET['remove']) || $_GET['remove'] != 'no';

        $this->download($filename, $file, $removeAfter);
    }

    /**
     * @param string $filename
     * @param string $file Absolute path to the file.
     * @param string $removeAfter
     */
    protected function download($filename, $file, $removeAfter = true)
    {
        ignore_user_abort(true);
        nocache_headers();

        $disabledFunction = explode(',', ini_get('disable_functions'));

        if (!in_array('set_time_limit', $disabledFunction)) {
            set_time_limit(0);
        }

        $mime = wp_check_filetype($file);
        $content = @file_get_contents($file);

        if ($removeAfter) {
            @unlink($file);
        }

        header('Content-Type: ' . $mime['type'] . '; charset=utf-8');
        header('Content-Disposition: attachment; filename=' . $filename);
        header('Expires: 0');

        // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
        echo $content;

        exit();
    }


    protected function forceUpgrader()
    {
        if ( !isset($_GET['mphb_action']) || 
            !mphb_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mphb_action'] )), 'mphb_notice_nonce' ) ) return;

        MPHB()->upgrader()->forceUpgrade();
    }

    protected function updateConfirmationEndpoints()
    {
        if ( !isset($_GET['mphb_action']) || 
            !mphb_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mphb_action'] )), 'mphb_notice_nonce') ) return;

        $bookingConfirmedId = MPHB()->settings()->pages()->getBookingConfirmedPageId();
        $reservationReceivedId = MPHB()->settings()->pages()->getReservationReceivedPageId();

        $pageContent = MPHB()->getShortcodes()->getBookingConfirmation()->generateShortcode();

        if ($bookingConfirmedId != 0) {
            wp_update_post(array(
                'ID' => $bookingConfirmedId,
                'post_content' => $pageContent
            ));
        }

        if ($reservationReceivedId != 0) {
            wp_update_post(array(
                'ID' => $reservationReceivedId,
                'post_content' => $pageContent
            ));
        }

        MPHB()->notices()->hideNotice( sanitize_text_field( wp_unslash( $_GET['mphb_action'] )) );
    }

    protected function hideNotice()
    {
        if ( !isset($_GET['mphb_action']) ||
            !mphb_verify_nonce( sanitize_text_field( wp_unslash( $_GET['mphb_action'] )), 'mphb_notice_nonce') ) return;

        if ( !isset($_GET['notice_id']) ) return;

        $noticeId = sanitize_text_field( wp_unslash( $_GET['notice_id'] ));

        MPHB()->notices()->hideNotice( $noticeId );
    }

    public function fireError($message)
    {
        if ( is_admin() ) {
            // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped
            wp_die( $message, esc_html__('Error', 'motopress-hotel-booking'), array('response' => 403) );
        }

        return false;
    }
}
