<?php
/**
 * 옵션 설정 페이지의 템플릿
 *
 * @var array{redirect_uri: string} $args
 */
?>
<h1 class="wp-heading-inline">페이스북 포스트 모니터</h1>
<hr class="wp-header-end">
<div class="wrap">
    <form method="post" action="<?php echo esc_url( admin_url( 'options.php' ) ); ?>">
		<?php
		settings_fields( 'fbpm' );
		do_settings_sections( 'fbpm' );
		submit_button();
		?>
    </form>

    <form method="post" action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>">
        <h2>인증</h2>
        <table class="form-table" role="presentation">
            <tbody>
            <tr>
                <th scope="row"><label for="redirection_uri">리디렉션 URI</label></th>
                <td>
                    <input autocomplete="off"
                           class="text large-text"
                           id="redirection_uri"
                           readonly="readonly"
                           type="url"
                           value="<?php echo esc_attr( $args['redirect_uri'] ); ?>"
                    >
                    <p class="description">
                        위 URI를 복사하여 메타 개발자 앱의 '유효한 OAuth 리다이렉션 URI' 설정에 붙여 넣으세요.
                    </p>
                </td>
            </tr>
            </tbody>
        </table>
        <input type="hidden" name="action" value="fbpm-authorize">
		<?php wp_nonce_field( 'fbpm-authorize' ); ?>
        <?php submit_button( '인증하기' ); ?>
    </form>
</div>
