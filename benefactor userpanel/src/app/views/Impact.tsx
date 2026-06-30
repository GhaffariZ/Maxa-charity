import React from "react";
import { 
  Heart, 
  Baby, 
  Stethoscope, 
  Activity, 
  ArrowRight,
  Sparkles,
  Quote
} from "lucide-react";
import { motion } from "motion/react";
import { ImageWithFallback } from "../components/figma/ImageWithFallback";

const impacts = [
  {
    title: "حمایت از کودکان",
    description: "کمک‌های شما باعث شده ۱۰ کودک مبتلا به بیماری‌های خاص در ماه گذشته تمام داروهای خود را به صورت رایگان دریافت کنند.",
    icon: Baby,
    color: "bg-primary",
    stat: "۱۰ کودک"
  },
  {
    title: "خدمات پزشکی",
    description: "بخشی از مشارکت شما صرف خرید ۳ دستگاه اکسیژن‌ساز جدید برای بخش مراقبت‌های ویژه شد.",
    icon: Stethoscope,
    color: "bg-secondary",
    stat: "۳ دستگاه"
  },
  {
    title: "مشاوره و مددکاری",
    description: "خانواده‌های تحت پوشش از ۲۴ ساعت خدمات مشاوره روانشناسی با حمایت شما بهره‌مند شدند.",
    icon: Activity,
    color: "bg-accent",
    stat: "۲۴ ساعت"
  }
];

export function Impact() {
  return (
    <div className="space-y-12 pb-12">
      <div className="text-center max-w-2xl mx-auto mb-16">
        <motion.div 
          initial={{ scale: 0.8, opacity: 0 }}
          animate={{ scale: 1, opacity: 1 }}
          className="w-20 h-20 bg-primary/10 text-primary rounded-full flex items-center justify-center mx-auto mb-6"
        >
          <Heart size={40} fill="currentColor" />
        </motion.div>
        <h2 className="text-4xl font-extrabold mb-4">اثر پرمهر شما</h2>
        <p className="text-muted-foreground text-lg">
          هر ریال از مشارکت شما، لبخندی بر لبی می‌نشاند و امیدی در دلی زنده می‌کند. 
          در اینجا می‌توانید خلاصه‌ای از تغییراتی که ایجاد کردید را ببینید.
        </p>
      </div>

      <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
        {impacts.map((item, idx) => (
          <motion.div
            key={item.title}
            initial={{ opacity: 0, y: 30 }}
            animate={{ opacity: 1, y: 0 }}
            transition={{ delay: idx * 0.1 }}
            className="bg-surface p-8 rounded-[2.5rem] border border-border hover:shadow-2xl hover:shadow-primary/5 transition-all relative overflow-hidden group"
          >
            <div className={`w-16 h-16 ${item.color} rounded-2xl flex items-center justify-center text-white mb-8 shadow-lg shadow-current/20 group-hover:scale-110 transition-transform`}>
              <item.icon size={32} />
            </div>
            <h3 className="text-2xl font-bold mb-4">{item.title}</h3>
            <p className="text-muted-foreground leading-relaxed mb-6">{item.description}</p>
            <div className="flex items-center justify-between mt-auto pt-6 border-t border-border">
              <span className="text-sm font-bold text-muted-foreground uppercase tracking-wider">دستاورد:</span>
              <span className={`font-extrabold text-lg ${item.color.replace('bg-', 'text-')}`}>{item.stat}</span>
            </div>
          </motion.div>
        ))}
      </div>

      {/* Story Section */}
      <div className="bg-surface rounded-[3rem] border border-border overflow-hidden flex flex-col lg:flex-row shadow-sm">
        <div className="lg:w-1/2 relative h-80 lg:h-auto">
          <img 
            src="https://images.unsplash.com/photo-1517486808906-6ca8b3f04846?auto=format&fit=crop&q=80&w=800" 
            className="w-full h-full object-cover"
            alt="Impact story"
          />
          <div className="absolute inset-0 bg-gradient-to-r from-surface via-transparent to-transparent hidden lg:block" />
        </div>
        <div className="lg:w-1/2 p-12 lg:p-20 flex flex-col justify-center">
          <Quote className="text-primary/20 mb-6 w-16 h-16" />
          <h3 className="text-3xl font-extrabold mb-6 leading-tight">داستان مهربانی: خانواده احمدی</h3>
          <p className="text-muted-foreground text-lg leading-relaxed mb-8 italic">
            "وقتی بیماری پسرم شدت گرفت، فکر نمی‌کردم کسی صدای ما را بشنود. اما با حمایت‌های شما، تمام هزینه‌های درمان تامین شد و امروز پسرم می‌تواند دوباره راه برود. هیچ کلمه‌ای برای تشکر از شما پیدا نمی‌کنم."
          </p>
          <div className="flex items-center gap-4">
            <div className="w-12 h-12 rounded-full bg-primary/10 flex items-center justify-center text-primary">
              <Sparkles size={24} />
            </div>
            <div>
              <p className="font-bold">مادر علی</p>
              <p className="text-sm text-muted-foreground text-left">مهر ۱۴۰۲</p>
            </div>
          </div>
        </div>
      </div>

      <div className="bg-primary p-12 rounded-[3rem] text-white text-center shadow-xl shadow-primary/20">
        <h3 className="text-3xl font-extrabold mb-4">هنوز کارهای زیادی هست که باید انجام دهیم</h3>
        <p className="text-primary-light text-lg mb-10 max-w-2xl mx-auto opacity-90">
          بیش از ۱۰۰ بیمار جدید در صف انتظار برای دریافت خدمات حمایتی هستند. با استمرار همراهی شما، هیچ بیماری تنها نخواهد ماند.
        </p>
        <button className="bg-secondary hover:bg-secondary/90 text-white px-12 py-5 rounded-2xl font-extrabold text-lg transition-all shadow-lg shadow-secondary/30">
          حمایت مجدد
        </button>
      </div>
    </div>
  );
}
