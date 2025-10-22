<?xml version="1.0" encoding="UTF-8"?>

<!-- Copyright (c) 2025 Antonio Daniele Gialluisi -->

<!-- This file is part of "UseCaseTableCreator" -->

<!-- Permission is hereby granted, free of charge, to any person obtaining a copy -->
<!-- of this software and associated documentation files (the "Software"), to deal -->
<!-- in the Software without restriction, including without limitation the rights -->
<!-- to use, copy, modify, merge, publish, distribute, sublicense, and/or sell -->
<!-- copies of the Software, and to permit persons to whom the Software is -->
<!-- furnished to do so, subject to the following conditions: -->

<!-- The above copyright notice and this permission notice shall be included in all -->
<!-- copies or substantial portions of the Software. -->

<xsl:stylesheet xmlns:xsl="http://www.w3.org/1999/XSL/Transform" version="1.0">
    <xsl:output method="html"/>

    <xsl:template match="/">
        <table style="text-align: left">
            <tbody>
                <xsl:apply-templates select="CU"/>
                <xsl:apply-templates select="ALT"/>
                <xsl:apply-templates select="CU/description"/>
                <xsl:apply-templates select="ALT/description"/>
                <xsl:apply-templates select="CU/primary-actors"/>
                <xsl:apply-templates select="ALT/primary-actors"/>
                <xsl:apply-templates select="CU/secondary-actors"/>
                <xsl:apply-templates select="ALT/secondary-actors"/>
                <xsl:apply-templates select="CU/preconditions"/>
                <xsl:apply-templates select="ALT/preconditions"/>
                <xsl:apply-templates select="ALT/execution-step"/>
                <xsl:apply-templates select="CU/sequence"/>
                <xsl:apply-templates select="ALT/sequence"/>
                <xsl:apply-templates select="CU/postconditions"/>
                <xsl:apply-templates select="ALT/postconditions"/>
                <xsl:apply-templates select="CU/alternative-sequences"/>
            </tbody>
        </table>
    </xsl:template>



    <xsl:template match="CU">
        <tr>
            <td>
                <b>ID</b>&#xA0;<u><i>CU_<xsl:value-of select="id" disable-output-escaping="yes"/></i></u>
            </td>
            <td>
                <b>Use case</b>&#xA0;<u><i><xsl:value-of select="name" disable-output-escaping="yes"/></i></u>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="ALT">
        <tr>
            <td>
                <b>ID</b>&#xA0;<u><i>ALT_<xsl:value-of select="id" disable-output-escaping="yes"/></i></u>
            </td>
            <td>
                <b>Alternative scenario</b>&#xA0;<u><i><xsl:value-of select="name" disable-output-escaping="yes"/></i></u>
            </td>
        </tr>
    </xsl:template>

    <xsl:template match="description">
        <tr>
            <td>Description</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="primary-actors">
        <tr>
            <td>Primary actors</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="secondary-actors">
        <tr>
            <td>Secondary actors</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="preconditions">
        <tr>
            <td>Preconditions</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="execution-step">
        <tr>
            <td>Execution step</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="sequence">
        <tr>
            <td>Sequence of events</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="postconditions">
        <tr>
            <td>Postconditions</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>

    <xsl:template match="alternative-sequences">
        <tr>
            <td>Alternative sequences</td>
            <td><xsl:value-of select="current()" disable-output-escaping="yes"/></td>
        </tr>
    </xsl:template>
</xsl:stylesheet>
