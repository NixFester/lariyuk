import re

input_file = "input.txt"
output_file = "emails.txt"

exclude = "noreply@run.healthcenterindonesia.com"

emails = set()

with open(input_file, "r", encoding="utf-8", errors="ignore") as f:
    text = f.read()

# More forgiving regex (handles punctuation around emails)
matches = re.findall(r'[a-zA-Z0-9._%+-]+@gmail\.com', text, re.IGNORECASE)

for email in matches:
    email = email.strip().lower().strip(".,;:()[]<>\"'")  # clean edges
    if email != exclude:
        emails.add(email)

with open(output_file, "w", encoding="utf-8") as f:
    for email in sorted(emails):
        f.write(email + "\n")

print(f"Found {len(emails)} gmail addresses")