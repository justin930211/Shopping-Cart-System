const header = document.querySelector("header"); //宣告header

window.addEventListener("scroll",()=>{ //檢查是否為false 是否觸發
	if(!skillsPlayed) skillsCounter();
	if (!mlPlayed)mlCounter();
});

function updateCount(num,maxNum){ //num DOM 元素,文字內容將被用來顯示數字計數  maxNum目標數字
	let currentNum = +num.innerText;


	if(currentNum<maxNum){ //檢查是否小於
		num.innerText=currentNum +1; //數字加一
		setTimeout(()=>{  //每0.012加一次
			updateCount(num,maxNum);
		},12);
	}
}

function stickyNavbar(){
	header.classList.toggle("scrolled",window.pageYOffset > 0); //如果大於0 在 header 上加scrolled css
}

stickyNavbar();  //執行

window.addEventListener("scroll", stickyNavbar);  //滾動時呼叫stickyNavbar

let sr = ScrollReveal({ //創建動畫 
	duration:2500, //時間2.5
	distance:"60px",//移動距離60
});