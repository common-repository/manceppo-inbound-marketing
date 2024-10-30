<?php
/**
 * Download form selector pop-up.
 *
 * @package manceppo
 */

namespace manceppo;

?>

<script type="text/javascript">
	let downloadFormsData = <?php echo _wp_json_convert_string( Manceppo_Admin::get_download_forms() ); // phpcs:ignore WordPress.Security.EscapeOutput.OutputNotEscaped ?>;
</script>
