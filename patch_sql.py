import re
import sys

def patch_sql(filepath):
    with open(filepath, 'r', encoding='utf-8') as f:
        content = f.read()

    # Find all ALTER TABLE modify auto_increment statements
    # Pattern looks for:
    # ALTER TABLE `table_name`
    #   MODIFY `col_name` type NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=value;
    
    pattern = re.compile(r"ALTER TABLE `([^`]+)`\s+MODIFY `([^`]+)` ([^,]+) AUTO_INCREMENT(?:,\s*AUTO_INCREMENT=(\d+))?;")
    matches = pattern.findall(content)
    
    for table_name, col_name, col_type, auto_val in matches:
        print(f"Patching table {table_name}, column {col_name}, auto_val {auto_val}")
        
        # 1. Add AUTO_INCREMENT to the column definition in CREATE TABLE
        # We look for CREATE TABLE `table_name` (... `col_name` type ... )
        create_table_pattern = re.compile(rf"(CREATE TABLE `{table_name}` \([^;]+?`{col_name}` {re.escape(col_type)})", re.MULTILINE | re.DOTALL)
        
        if auto_val:
            # Add AUTO_INCREMENT to column and AUTO_INCREMENT=val to table end
            content = create_table_pattern.sub(r"\1 AUTO_INCREMENT", content)
            # Find the closing bracket of the CREATE TABLE to append AUTO_INCREMENT=val
            # It looks like: ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
            close_pattern = re.compile(rf"(CREATE TABLE `{table_name}` \([^;]+?\)[^;]+);", re.MULTILINE | re.DOTALL)
            content = close_pattern.sub(rf"\1 AUTO_INCREMENT={auto_val};", content)
        else:
            content = create_table_pattern.sub(r"\1 AUTO_INCREMENT", content)
            
    # Now remove all ALTER TABLE ... MODIFY lines that have AUTO_INCREMENT
    content = re.sub(r"ALTER TABLE `[^`]+`\s+MODIFY `[^`]+` [^,;]+ AUTO_INCREMENT[^;]*;", "", content)
    
    with open("gomhuong1_appnew_patched.sql", 'w', encoding='utf-8') as f:
        f.write(content)
    
    print("Patched SQL saved to gomhuong1_appnew_patched.sql")

if __name__ == "__main__":
    patch_sql("gomhuong1_appnew.sql")
