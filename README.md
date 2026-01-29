# Shopping-Cart-System
全端電子商務購物車系統 (PHP & MySQL)
這是一個獨立開發的完整電商平台專案，涵蓋了從前端介面設計、非同步資料校驗到後端管理系統的開發。本專案重點展示了對資料庫關聯設計、使用者權限控管以及 Web 資安防護的實作能力。

🚀 技術棧 (Tech Stack)
前端 (Frontend): HTML5, CSS3, JavaScript, jQuery (AJAX)
後端 (Backend): PHP
資料庫 (Database): MySQL (MariaDB)
其他: Responsive Design, SQL Prepared Statements
🌟 核心功能亮點
1. 使用者端 (Customer Side)
非同步註冊校驗：使用 jQuery AJAX 技術實作手機號碼即時查重與密碼強度驗證，提升用戶註冊流暢度。
購物車邏輯管理：實作即時更新商品數量、小計與總額計算功能。
訂單自動化處理：下單後自動扣除庫存，若訂單取消則具備庫存自動回歸機制，確保資料一致性。
資安與體驗優化：利用 window.history.replaceState 防止表單重複遞送，並對密碼進行格式檢核。
2. 管理員後台 (Admin Side)
權限分級防護：實作 Session 驗證機制，嚴格控管非管理員帳號非法進入管理頁面。
商品 CRUD 與管理：支援商品上傳（圖片上傳與縮圖處理）、下架邏輯以及庫存即時調整。
會員與訂單監控：管理員可進行會員封鎖/解封，並追蹤每筆訂單的狀態（未出貨、已結單、已取消）。
🔒 安全防護實作
防範 SQL 注入 (SQL Injection)：針對管理端的核心查詢與更新動作，採用 Prepared Statements（預處理語句）來強化資料庫安全性。
輸入過濾：在前端與後端皆實作正規表達式（Regex）校驗，確保手機號碼與密碼格式正確。
