/**
 * ブロックバリエーションの登録解除
 *
 * @link https://developer.wordpress.org/news/2023/08/29/an-introduction-to-block-variations/
 */
wp.domReady(() => {
    const embedVariations = [
        'animoto',
        'daylymotion',
        'hulu',
        'reddit',
        'tumblr',
        'vine',
        'amazon-kindle',
        'cloudup',
        'crowdsignal',
        'speaker',
        'scribd',
        'slideshare',
        'smugmug',
        'spotify',
        'issuu',
        'kickstarter',
    ];

    embedVariations.forEach( ( variation ) => {
        wp.blocks.unregisterBlockVariation(
            'core/embed',
            variation
        );
    } );
} );

/**
 * ブロックスタイルの登録を解除
 */
wp.domReady( function() {
    wp.blocks.unregisterBlockStyle(
        'core/image',
        [
            'default',
            'rounded'
        ]
    );
} );

/**
 * ブロックディレクトリを無効化する（JS版）（PHPでも同様の処理が可能）
 */
wp.domReady( function() {
    wp.plugins.unregisterPlugin( 'block-directory' );
} );

wp.domReady( function() {
    const formatsToUnregister = [
        'core/code',
        'core/image',
        'core/keyboard',
        'core/language',
        'core/strikethrough',
    ];

    formatsToUnregister.forEach( ( format ) => {
        wp.richText.unregisterFormatType( format );
    } );
} );