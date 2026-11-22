// Eleventy builds the site from src/ into _site/.
// Pages in src/*.html hold only their own content; the shared header and footer
// live in src/_includes/layouts/base.njk, and contact details in src/_data/site.json.
export default function (eleventyConfig) {
  // Files copied to the site as they are.
  eleventyConfig.addPassthroughCopy("src/styles.css");
  eleventyConfig.addPassthroughCopy("src/menu.js");
  eleventyConfig.addPassthroughCopy("src/form.js");
  eleventyConfig.addPassthroughCopy("src/contact.php");
  eleventyConfig.addPassthroughCopy("src/images");

  // Keep the existing addresses: src/about-contact.html -> about-contact.html
  // (Eleventy would otherwise make about-contact/index.html).
  eleventyConfig.addGlobalData("permalink", () => (data) => `${data.page.filePathStem}.html`);

  return {
    dir: { input: "src", output: "_site", includes: "_includes", data: "_data" },
    templateFormats: ["html", "njk"],
    htmlTemplateEngine: "njk",
  };
}
