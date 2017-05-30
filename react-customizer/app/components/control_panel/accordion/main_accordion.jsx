import React from 'react';
import ReactCSSTransitionGroup from 'react-addons-css-transition-group';
import AccordionContent from './accordion_content.jsx';

export default class MainAccordion extends React.Component {
  constructor(props){
    super(props);

    this.state = {
      activeOptionIndex: 0,
    }
  }

  setOptionAsActive(optionIndex){
    let newIndex = optionIndex;
    this.setState({activeOptionIndex: newIndex})
    this.props.setActiveOption(this.props.accordionOptions[newIndex])
  }

  render(){
    let options = this.props.accordionOptions.map((accordionOption, i) => {
      return(
        <div
          className={` accordion-item ${i === this.state.activeOptionIndex ? 'is-active' : ''} title`}
          key={i}
         >
          <a onClick={(()=>{this.setOptionAsActive(i, accordionOption)})} className="accordion-title" role="tab">
            {accordionOption['name']}
          </a>
          <AccordionContent
            {...this.props}
            isActive={i === this.state.activeOptionIndex}
          />
        </div>
      )
    });

    return(
      <div className="accordion" data-accordion role="tablist">
        <ReactCSSTransitionGroup transitionName="ease" transitionEnterTimeout={500} transitionLeaveTimeout={300}>
          {options}
        </ReactCSSTransitionGroup>
      </div>
    )
  }
}
