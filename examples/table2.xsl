<?xml version='1.0' encoding="ISO-8859-1"?>
<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
<xsl:param name="numberofcols" select="1"/>
<xsl:output method="html" indent="yes"/>

<xsl:template match="/">
    <xsl:apply-templates select="/items">
        <xsl:with-param name="c" select="$numberofcols"/>
    </xsl:apply-templates>
</xsl:template>

<!-- The template for the items to be printed into a table. This
     is where the <table> tag gets printed. -->
<xsl:template match="items">
  <xsl:param name="c" select="1"/>
  <p>
  <span class="xtext"><xsl:apply-templates select="link|text()"/></span>

  <!-- table rows are built with general purpose table routine: "makeRows"
       "c" is the number of columns
       "content" is the nodeset of items to be put in cells
       "counter" is always 1   -->
  <table border="1" cellspacing="4">
  <xsl:call-template name="makeRows">
    <xsl:with-param name="content" select="item"/>
    <xsl:with-param name="c" select="$c"/>
    <xsl:with-param name="counter" select="1"/>
  </xsl:call-template>
  </table>

  </p>
</xsl:template>
<!-- The template for printing the table rows -->
<xsl:template name="makeRows">

  <xsl:param name="content"/>
  <xsl:param name="c"/>

  <!-- print c cols (one row) -->
  <tr>
  <xsl:call-template name="makeCols">
    <xsl:with-param name="content" select="$content[position() &lt; ($c + 1)]"/>
    <xsl:with-param name="c" select="$c"/>
    <xsl:with-param name="counter" select="1"/>
  </xsl:call-template>
  </tr>
  <!-- recurse to print the remaining rows -->
  <xsl:if test="$content[position() &gt; $c]">
    <xsl:call-template name="makeRows">
      <xsl:with-param name="content" select="$content[position() &gt; $c]"/>
      <xsl:with-param name="c" select="$c"/>
    </xsl:call-template>
  </xsl:if>

</xsl:template>
<!-- The template for printing the table columns -->
<xsl:template name="makeCols">

  <xsl:param name="content"/>
  <xsl:param name="c"/>
  <xsl:param name="counter"/>

  <!--print out c (number of) cells-->
  <xsl:if test="not($counter &gt; $c)">
    <td>THISXSL2
    <xsl:if test="$content">
      <xsl:value-of select="$c"/>
      <xsl:apply-templates select="$content[1]"/>
    </xsl:if>
    </td>
    <!-- if there are any left, recurse-->
    <xsl:call-template name="makeCols">
       <xsl:with-param name="content" select="$content[position() != 1]"/>
       <xsl:with-param name="c" select="$c"/>
       <xsl:with-param name="counter" select="$counter + 1"/>
    </xsl:call-template>
  </xsl:if>

</xsl:template>

</xsl:stylesheet>

