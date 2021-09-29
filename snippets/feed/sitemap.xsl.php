<?= '<?xml version="1.0" encoding="UTF-8"?>' ?>
<xsl:stylesheet version="2.0"
                xmlns:html="http://www.w3.org/TR/REC-html40"
                xmlns:image="http://www.google.com/schemas/sitemap-image/1.1"
                xmlns:sitemap="http://www.sitemaps.org/schemas/sitemap/0.9"
                xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:output method="html" version="1.0" encoding="UTF-8" indent="yes"/>
  <xsl:template match="/">
    <html xmlns="http://www.w3.org/1999/xhtml">
      <head>
        <title>XML Sitemap</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
      </head>
      <body>
        <div id="content">
          <h1>XML Sitemap</h1>
          <table id="sitemap" cellpadding="3">
            <thead>
              <tr>
                <th width="50%">URL</th>
                <th width="5%">Last Change</th>
                <th width="5%">Images Count</th>
                <th width="40%">Images</th>
              </tr>
            </thead>
            <tbody>
              <xsl:for-each select="sitemap:urlset/sitemap:url">
                <tr>
                  <td>
                    <xsl:variable name="itemURL">
                      <xsl:value-of select="sitemap:loc"/>
                    </xsl:variable>
                    <a href="{$itemURL}">
                      <xsl:value-of select="sitemap:loc"/>
                    </a>
                  </td>
                  <td>
                    <xsl:value-of select="concat(substring(sitemap:lastmod,0,11),concat(' ', substring(sitemap:lastmod,12,5)))"/>
                  </td>
                  <td>
                    <xsl:value-of select="count(image:image)"/>
                  </td>
                  <td>
                    <xsl:for-each select="image:image">
                      <xsl:variable name="imageURL">
                        <xsl:value-of select="image:loc"/>
                      </xsl:variable>
                      <img style="width: 42px;" src="{$imageURL}" />
                    </xsl:for-each>
                  </td>
                </tr>
              </xsl:for-each>
            </tbody>
          </table>
        </div>
      </body>
    </html>
  </xsl:template>
</xsl:stylesheet>