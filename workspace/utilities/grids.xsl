<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:template name="grid-16">
	<xsl:param name="js" select="'mootools'"/>
	<html>
		<head>
			<meta http-equiv="content-type" content="text/html; charset=utf-8" />
			<title><xsl:value-of select="$website-name"/> | 16-column Grid</title>
			<xsl:call-template name="grid-css"/>
		</head>
		<body>
			<div class="container_16">
				<div class="grid_16">
					<xsl:call-template name="grid-branding"/>
				</div>
				<div class="clear"></div>
				<div class="grid_16">
					<xsl:call-template name="grid-main-navigation"/>
				</div>
				<div class="clear"></div>
				<div class="grid_16">
					<xsl:call-template name="grid-page-heading"/>
				</div>
				<div class="clear"></div>
				
				<div class="grid_4">
					<xsl:call-template name="grid-textbox-1"/>
				</div>
				<div class="grid_4">
					<xsl:call-template name="grid-textbox-2"/>
				</div>
				<div class="grid_4">
					<xsl:call-template name="grid-textbox-3"/>
				</div>
				<div class="grid_4">
					<xsl:call-template name="grid-textbox-4"/>
				</div>
				<div class="clear"></div>

				<xsl:call-template name="grid16-boxes"/>

				<div class="grid_16">
					<xsl:call-template name="grid-kwick-box"/>
				</div>
				<div class="clear"></div>
				<div class="grid_4">
					<xsl:call-template name="grid-paragraphs"/>
					<xsl:call-template name="grid-ajax-content"/>
					<xsl:call-template name="grid-section-menu"/>
					<xsl:call-template name="grid-list-items"/>
				</div>
				<div class="grid_7">
					<xsl:call-template name="grid-accordion"/>
					<xsl:call-template name="grid-blockquote"/>
					<xsl:call-template name="grid-tables"/>
					<xsl:call-template name="grid-forms"/>
				</div>
				<div class="grid_5">
					<xsl:call-template name="grid-search"/>
					<xsl:call-template name="grid-login-forms"/>
					<xsl:call-template name="grid-articles"/>
				</div>
				<div class="clear"></div>
				<div class="grid_16" id="site_info">
					<xsl:call-template name="grid-site-info"/>
				</div>
				<div class="clear"></div>
			</div>
			<xsl:call-template name="include-javascript">
				<xsl:with-param name="js" select="$js"/>
			</xsl:call-template>
		</body>
	</html>
</xsl:template>

<xsl:template name="include-javascript">
	<xsl:param name="js" select="'mootools'"/>
	<xsl:choose>
		<xsl:when test="$js = 'mootools'">
			<xsl:call-template name="grid-mootools"/>
		</xsl:when>
		<xsl:when test="$js = 'jquery'">
			<xsl:call-template name="grid-jquery"/>
		</xsl:when>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>