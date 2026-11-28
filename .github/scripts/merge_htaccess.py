"""Add the site's security rules to the server's existing .htaccess.

Usage: python3 merge_htaccess.py <existing .htaccess or empty file> <output path>

The server already has a .htaccess (from WordPress and possibly the host, for
example the PHP version setting). Replacing it could break the site, so this
keeps everything that is there and puts our rules in their own marked section
at the top. On later runs the old section is replaced, so it never duplicates.

Prints only a summary, never the file's contents (the Actions log is public).
"""
import re
import sys

BEGIN = "# BEGIN Coastline Safety Services"
END = "# END Coastline Safety Services"

RULES = f"""{BEGIN}
# Added by the website's GitHub deploy. Anything between these two lines is
# replaced on every deploy; put your own rules outside this section.

# Send every http:// visit to the same address on https://
<IfModule mod_rewrite.c>
RewriteEngine On
RewriteCond %{{HTTPS}} !=on
RewriteCond %{{HTTP:X-Forwarded-Proto}} !=https
RewriteRule ^ https://%{{HTTP_HOST}}%{{REQUEST_URI}} [L,R=301]
</IfModule>

# HSTS: tell browsers to always use https for this site for the next year.
# (Not applied to subdomains, so mail or other services are unaffected.)
<IfModule mod_headers.c>
Header always set Strict-Transport-Security "max-age=31536000"
</IfModule>
{END}
"""

existing_path, output_path = sys.argv[1], sys.argv[2]

with open(existing_path, encoding="utf-8", errors="surrogateescape", newline="") as f:
    existing = f.read()

newline = "\r\n" if "\r\n" in existing else "\n"
text = existing.replace("\r\n", "\n")

had_block = BEGIN in text
text = re.sub(re.escape(BEGIN) + r".*?" + re.escape(END) + r"\n?", "", text, flags=re.S)
rest = text.lstrip("\n")

merged = RULES + ("\n" + rest if rest else "")
if not merged.endswith("\n"):
    merged += "\n"

with open(output_path, "w", encoding="utf-8", errors="surrogateescape", newline="") as f:
    f.write(merged.replace("\n", newline))

kept = [line for line in rest.splitlines() if line.strip()]
print(f"Existing .htaccess: {len(existing.splitlines())} lines "
      f"({'replacing our previous section' if had_block else 'adding our section for the first time'}).")
print(f"Kept {len(kept)} existing non-empty lines unchanged below our section.")
print("Existing file mentions WordPress:", "yes" if "WordPress" in rest else "no",
      "| PHP settings:", "yes" if re.search(r"(?im)^\s*(AddHandler|SetHandler|php_)", rest) else "no")
