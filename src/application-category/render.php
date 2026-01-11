<?php
$categories = [
    'GameApplication'               => 'Game application',
	'SocialNetworkingApplication'   => 'Social networking application',
	'TravelApplication'             => 'Travel application',
	'ShoppingApplication'           => 'Shopping application',
	'SportsApplication'             => 'Sports application',
	'LifestyleApplication'          => 'Lifestyle application',
	'BusinessApplication'           => 'Business application',
	'DesignApplication'             => 'Design application',
	'DeveloperApplication'          => 'Developer application',
	'DriverApplication'             => 'Driver application',
	'EducationalApplication'        => 'Educational application',
	'HealthApplication'             => 'Health application',
	'FinanceApplication'            => 'Finance application',
	'SecurityApplication'           => 'Security application',
	'BrowserApplication'            => 'Browser application',
	'CommunicationApplication'      => 'Communication application',
	'DesktopEnhancementApplication' => 'Desktop enhancement application',
	'EntertainmentApplication'      => 'Entertainment application',
	'MultimediaApplication'         => 'Multimedia application',
	'HomeApplication'               => 'Home application',
	'UtilitiesApplication'          => 'Utilities application',
	'ReferencetApplication'         => 'Reference application',
];

$category_id = get_post_meta( $block->context['postId'], '_dc23_software_category', true );
$category    = $categories[ $category_id ] ?? '';
?>
<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo esc_html( $category ); ?>
</div>
