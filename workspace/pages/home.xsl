<?xml version="1.0" encoding="UTF-8"?>
<xsl:stylesheet version="1.0"
	xmlns:xsl="http://www.w3.org/1999/XSL/Transform">

<xsl:import href="../utilities/grid-modules.xsl"/>
<xsl:import href="../utilities/grids.xsl"/>

<xsl:output method="xml"
	doctype-public="-//W3C//DTD XHTML 1.0 Strict//EN"
	doctype-system="http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd"
	omit-xml-declaration="yes"
	encoding="UTF-8"
	indent="yes" />

<xsl:template match="/">
	<xsl:choose>
		<xsl:when test="$c = 'jquery'">
			<xsl:choose>
				<xsl:when test="$b = 'fixed'">
					<xsl:choose>
						<xsl:when test="$a = '12'">
							<xsl:call-template name="grid-12">
								<xsl:with-param name="js" select="'jquery'"/>
								<xsl:with-param name="css" select="'fixed'"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="grid-16">
								<xsl:with-param name="js" select="'jquery'"/>
								<xsl:with-param name="css" select="'fixed'"/>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="$a = '12'">
							<xsl:call-template name="grid-12">
								<xsl:with-param name="js" select="'jquery'"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="grid-16">
								<xsl:with-param name="js" select="'jquery'"/>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:when>
		<xsl:when test="$c = 'none'">
			<xsl:choose>
				<xsl:when test="$b = 'fixed'">
					<xsl:choose>
						<xsl:when test="$a = '12'">
							<xsl:call-template name="grid-12">
								<xsl:with-param name="js" select="'none'"/>
								<xsl:with-param name="css" select="'fixed'"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="grid-16">
								<xsl:with-param name="js" select="'none'"/>
								<xsl:with-param name="css" select="'fixed'"/>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="$a = '12'">
							<xsl:call-template name="grid-12">
								<xsl:with-param name="js" select="'none'"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="grid-16">
								<xsl:with-param name="js" select="'none'"/>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:when>
		<xsl:otherwise>
			<xsl:choose>
				<xsl:when test="$b = 'fixed'">
					<xsl:choose>
						<xsl:when test="$a = '12'">
							<xsl:call-template name="grid-12">
								<xsl:with-param name="css" select="'fixed'"/>
							</xsl:call-template>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="grid-16">
								<xsl:with-param name="css" select="'fixed'"/>
							</xsl:call-template>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:when>
				<xsl:otherwise>
					<xsl:choose>
						<xsl:when test="$a = '12'">
							<xsl:call-template name="grid-12"/>
						</xsl:when>
						<xsl:otherwise>
							<xsl:call-template name="grid-16"/>
						</xsl:otherwise>
					</xsl:choose>
				</xsl:otherwise>
			</xsl:choose>
		</xsl:otherwise>
	</xsl:choose>
</xsl:template>

</xsl:stylesheet>